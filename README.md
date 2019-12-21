# CustomTranslator
CS 174 Final Project

This web application allows users to upload two of their own dictionary files and translates words they input based on the content of those files. Used PHP, MySQL, and JavaScript.

- Handled user authentication via salting and hashing to the MySQL database.
- Utilized superglobal variables for session management in PHP to ensure user is authenticated.
- Put dictionary filepaths in the MySQL database for the default user and each individual user. PHP can read from these respective files using file locking mechanisms.
- Validated field inputs from the user on the client side (JavaScript) and server side (PHP) by using regular expressions.
