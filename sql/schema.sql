/*
   During development this gives me a way to wipe
   my tables when needed. Will not be used during
   production, strictly a tool for me.
*/
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS project_members;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS users;

-- Users table with columns for id, username, email, password and created_at
CREATE TABLE users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(50) NOT NULL UNIQUE,
	email VARCHAR(100) NOT NULL UNIQUE,
	password_hash VARCHAR(255) NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

/*
   Projects table. Keeps track of the owner of each project, project name, a description of the project, when it was created and a link to link a user to the owner_id if they own it.

*/
CREATE TABLE projects (
	id INT AUTO_INCREMENT PRIMARY KEY,
	owner_id INT NOT NULL,
	title VARCHAR(100) NOT NULL,
	description TEXT,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (owner_id) REFERENCES users(id)
);

/*
   The members of the project. Keeps track of the associated project, the associated user(s), the role of the user and also a check to make sure the project and the user exists. We use UNIQUE to make sure the same user cannot get added to the same project more than once. This is my join table for the users and projects tables.
*/

CREATE TABLE project_members (
	id INT AUTO_INCREMENT PRIMARY KEY,
	project_id INT NOT NULL,
	user_id INT NOT NULL,
	role VARCHAR(50) DEFAULT 'member',
	FOREIGN KEY (project_id) REFERENCES projects(id),
	FOREIGN KEY (user_id) REFERENCES users(id),
	UNIQUE(project_id, user_id)
);

/*
   This table stores all tasks within the project. project_id will link a task to a project. Tasks may be given a title and description. Status will be for my Kanban board I will be working on later. assigned_to and due_date will be used for my advanced feature, allowing tasks to be assigned to specific project members and an optional due date. created_at shows when the task was created. My two foreign keys ensure that the project and assigned user do exist.


*/

CREATE TABLE tasks (
	id INT AUTO_INCREMENT PRIMARY KEY,
	project_id INT NOT NULL,
	title VARCHAR(100) NOT NULL,
	description TEXT,
	status ENUM('todo', 'in_progress', 'done') DEFAULT 'todo',
	assigned_to INT DEFAULT NULL,
	due_date DATE DEFAULT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (project_id) REFERENCES projects(id),
	FOREIGN KEY (assigned_to) REFERENCES users(id)
);


