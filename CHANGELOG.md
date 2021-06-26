# Changelog

All notable changes to this project will be documented in this file.

This project adheres to Semantic Versioning.

## 1.1.3 - 2021-06-27

- Fixed error when the database name set dynamically by the application.

## 1.1.2 - 2020-11-30

- Change default host from 0.0.0.0 to 127.0.0.1 because in windows can't use 0.0.0.0.
- Change default port from 9001 to 9002 because in windows there is possibility the port will be used by the system.

## 1.1.1 - 2020-11-29

- Update code to make it can use in Lumen.

## 1.1.0 - 2020-11-29

- Use ReactPHP event loop and socket to monitor the SQL query executed from application.

## 1.0.0 - 2020-11-25

- Initial release! 🎉