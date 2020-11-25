<?php

namespace Muhdfaiz\LaravelTailDb;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Helper\Table;

class TailDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tail:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tail latest database query executed with automatically running explain query.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Run tail command for the database log and display the executed sql query, time it take to complete the
     * query, location that trigger the sql query and able to automatically output explain result for the
     * sql query.
     */
    public function handle()
    {
        $filename = config('tail-db.filename');
        $path = config('tail-db.path');

        // Check file exist. If not exist create empty log file.
        if (!file_exists( $path . '/' . $filename)) {
            file_put_contents($path . '/' . $filename, '');
        }

        // Show an error message if Laravel Tail DB not enabled.
        if (!$this->checkStatusEnabled()) {
            $this->output->writeln(PHP_EOL);
            $this->output->writeln('<error> Laravel Tail DB has been disabled. Please enabled first before' .
                ' running the command. </error>');
            $this->output->writeln(PHP_EOL);
            return;
        }

        $this->output->writeln('<info>Listening for new query from application.....</info>' . PHP_EOL);

        $tailCommand = $this->generateTailCommand();

        Process::fromShellCommandline($tailCommand, config('tail-db.path'))
            ->setTty(false)
            ->setTimeout(null)
            ->run(function ($type, $logData) {
                if (!$logData) {
                    return;
                }

                $logData = $this->parseLogData($logData);

                $this->outputQueryDetails($logData);

                $databaseDriver = config('database.connections.' . $logData['connection'] . '.driver');

                // For SQL Server, don't have explain command like SQLite, MySQL and PostgreSQL.
                // Need to check if the database using SQL Server or not.
                // If using SQL Server, disable the explain command.
                if (config('tail-db.show_explain') && $databaseDriver !== 'sqlsrv'
                    && preg_match('(select|delete|insert|replace|update)', $logData['sql']) === 1) {
                    $this->outputExplainResultInTableFormat($logData);
                }

                if (config('tail-db.clear_log')) {
                    $this->clearLogFile();
                }
            });
    }

    /**
     * Check if Laravel Tail DB enabled or not.
     *
     * @return bool
     */
    protected function checkStatusEnabled()
    {
        return config('tail-db.enabled');
    }

    /**
     * Generate tail command.
     *
     * @return string
     */
    public function generateTailCommand()
    {
        // If running in windows, tail command not available.
        // Need to use powershell
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Needed this because if using -f option in test, the command will be running forever.
            // If the test contain code running tail:db command, the test will not stop.
            // Didn't find a way to stop/terminate terminate the command..
            if (config('app.env') === 'testing') {
                return 'powershell Get-Content ' . config('tail-db.filename') . ' -Tail 1';
            } else {
                return 'powershell Get-Content ' . config('tail-db.filename') . ' -Wait -Tail 0';
            }
        } else {
            // Needed this because if using -f option in test, the command will be running forever.
            // If the test contain code running tail:db command, the test will not stop.
            // Didn't find a way to stop/terminate terminate the command..
            if (config('app.env') === 'testing') {
                return 'tail -n 1 ' . config('tail-db.filename');
            } else {
                return 'tail -f -n 0 ' . config('tail-db.filename');
            }
        }
    }

    /**
     *  Parse the log data in JSON into array.
     *
     * @param string $logData
     *
     * @return array
     */
    protected function parseLogData(string $logData)
    {
        $needle = config('app.env') . '.INFO: ';

        $databaseQueryJSON = str_replace($needle, '', strstr($logData, $needle));
        $databaseQueryArray = json_decode($databaseQueryJSON, true);

        return $databaseQueryArray;
    }

    /**
     * Execute explain query based on query receive from tail process.
     *
     * @param array $logData
     *
     * @return array
     */
    protected function executeExplainQuery(array $logData)
    {
        $explainQuery = 'explain ' . str_replace('Query: ', '', $logData['sql']);
        $explainResult = DB::connection($logData['connection'])->select($explainQuery);

        return $explainResult;
    }

    /**
     * Output the last sql query executed and time taken to complete the query.
     * Also output the file that trigger the last sql query executed.
     *
     * @param array $logData
     */
    protected function outputQueryDetails(array $logData)
    {
        $timeColor = 'blue';

        if ($logData['time'] > config('tail-db.slow_duration')) {
            $timeColor = 'red';
        }

        $file = '<options=bold>File:</> <info>' . $logData['file'] . '</info>  <options=bold>Line:</> <info>' . $logData['line'] . '</info>';
        $query = '<options=bold>Query:</> <info>' . $logData['sql'] . '</info>  <options=bold>Time:</> <fg=' . $timeColor . '>' . $logData['time'] . ' ms</>';

        $this->output->writeln($file);
        $this->output->writeln($query);

        if (!config('tail-db.show_explain')) {
            $this->output->write(PHP_EOL);
        }
    }

    /**
     * Output the data from explain result in the console using table format..
     *
     * @param array $logData
     */
    protected function outputExplainResultInTableFormat(array $logData)
    {
        $databaseDriver = config('database.connections.' . $logData['connection'] . '.driver');

        $explainResult = $this->executeExplainQuery($logData);

        $tableHeaders = $this->getTableHeaderFromExplainResult($explainResult, $databaseDriver);
        $tableRows = $this->generateTableRows($explainResult, $databaseDriver);

        $table = new Table($this->output);

        // For postgreSql, the explain result only have 1 columns and 1 rows.
        // Need to set max width to support screen with smaller width.
        if ($databaseDriver === 'pgsql') {
            $table->setColumnMaxWidth(0, 120);
        } else if ($databaseDriver === 'mysql') {
            $table->setColumnMaxWidth(5, 60);
            $table->setColumnMaxWidth(6, 60);
        }

        $table->setStyle('box')->setHeaders($tableHeaders)
            ->setRows($tableRows)->render();

        $this->output->write(PHP_EOL);
    }

    /**
     * Generate table header for the explain result.
     *
     * @param array $explainResult
     * @param string $databaseDriver
     *
     * @return array
     */
    protected function getTableHeaderFromExplainResult(array $explainResult, string $databaseDriver)
    {
        $headers = array_keys((array) $explainResult[0]);

        if ($databaseDriver === 'mysql') {
            unset($headers[3]);
        }

        return $headers;
    }

    /**
     * Generate table header for the explain result.
     *
     * @param array $explainResult
     * @param string $databaseDriver
     *
     * @return array
     */
    protected function generateTableRows(array $explainResult, string $databaseDriver)
    {
        $rows = [];

        foreach ($explainResult as $key => $result) {
            $rows[$key] = array_values((array) $result);

            // Trying to support smaller screen by trying to avoid the text length too long
            // by split the string into a few line.
            // Only for MySQL.
            if ($databaseDriver === 'mysql') {
                // Split string into multiple lines.
                if (!empty($rows[$key][5])) {
                    $rows[$key][5] = str_replace(',', PHP_EOL, $rows[$key][5]);
                }

                // Split string into multiple lines.
                if (!empty($rows[$key][6])) {
                    $rows[$key][6] = str_replace(',', PHP_EOL, $rows[$key][6]);
                }

                // Round filtered value to 2 decimal place.
                if (!empty($rows[$key][10])) {
                    $rows[$key][10] = round($rows[$key][10], 2);
                }

                // Split string into multiple lines.
                if (!empty($rows[$key][11])) {
                    $rows[$key][11] = str_replace('; ', PHP_EOL, $rows[$key][11]);
                }

                unset($rows[$key][3]);
            }
        }

        return $rows;
    }

    /**
     * Clear log file uses to record database query.
     */
    protected function clearLogFile()
    {
        // Clear log file.
        $filename = config('tail-db.filename');
        $path = config('tail-db.path');

        fclose(fopen($path . '/' . $filename,'w'));
    }
}
