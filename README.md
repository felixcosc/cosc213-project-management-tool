# Project Management Tool
A LAMP-based task organizer and project creator for team collaboration

## Team Members
* Felix Serwa

## Features
### Secure User Authentication
* Registration and login system
* Password hashing

### Project Management
* Create and modify projects
* Project owner can invite users via user/email
* Users can view all owned/shared projects on their personal dashboard

### Task Management
* Simple CRUD operations
* Task status updates (To-Do, In Progress, Done) can be updated by both owner and member
* Optional due dates
* Assign specific tasks to specific members
* Each project has a unique task view

### Task Discussion
* Timestamped comments per task
* Owners and members are able to comment

### Technologies Utilized
* Server - Apache 2
* Backend - PHP 8
* Database - MySQL
* Frontend - HTML5, CSS3
* Version Control - Git + Github

### Installation and Setup
* 1. Clone the Repo
```
git clone https://github.com/felixcosc/cosc213-project-management-tool.git
cd cosc213-project-management-tool
```
* 2. Move Files to Your Server Root
For XAMPP
```
htdocs/project_management_tool/
```
For LAMP
```
/var/www/html/project_management_tool/
```
*3. Create Database
Open phpMyAdmin
Create a new database called:
```
project_tool
```
Import schema file
```
sql/schema.sql
```
*4. Configure Database Connection
Edit:
```
reusable/db.php
```
Update it with your local mySQL info
```
$host = '127.0.0.1';
$user = 'project_user';
$pass = 'Pass1234';
$dbname = 'project_tool';
```
*5. Run
Open your browser:
```
http://localhost/project_management_tool/login.html
```
You should see the login page

Sample account to create:
```
username: admin
email: admin@example.com
password: pass123
```
### Final Notes
* Please contact me at iamahzoolah@gmail.com if you have any issues
* I used OpenAI for assistance on structuring this README.md file to make sure I was not missing anything, all of my writing and wording is my own

