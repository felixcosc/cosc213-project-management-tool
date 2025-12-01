
-- Users table with columns for id, username, email, password and created_at
CREATE TABLE IF NOT EXISTS users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(50) NOT NULL UNIQUE,
	email VARCHAR(100) NOT NULL UNIQUE,
	password_hash VARCHAR(255) NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

/*
   Projects table. Keeps track of the owner of each project, project name, a description of the project, when it was created and a link to link a user to the owner_id if they own it.

*/
CREATE TABLE IF NOT EXISTS projects (
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

CREATE TABLE IF NOT EXISTS project_members (
	id INT AUTO_INCREMENT PRIMARY KEY,
	project_id INT NOT NULL,
	user_id INT NOT NULL,
	role VARCHAR(50) DEFAULT 'member',
	FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE, 
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
	UNIQUE(project_id, user_id)
);

/*
   This table stores all tasks within the project. project_id will link a task to a project. Tasks may be given a title and description. Status will be for my Kanban board I will be working on later. assigned_to and due_date will be used for my advanced feature, allowing tasks to be assigned to specific project members and an optional due date. created_at shows when the task was created. My two foreign keys ensure that the project and assigned user do exist.


*/

CREATE TABLE IF NOT EXISTS tasks (
	id INT AUTO_INCREMENT PRIMARY KEY,
	project_id INT NOT NULL,
	title VARCHAR(100) NOT NULL,
	description TEXT,
	status ENUM('todo', 'in_progress', 'done') DEFAULT 'todo',
	assigned_to INT DEFAULT NULL,
	due_date DATE DEFAULT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
	FOREIGN KEY (assigned_to) REFERENCES users(id)
);

/*
   This table stores task_comments data. Each comment has a task_id and user_id. Comment text must
   be made. created_at stores when the comment was made. The foreign keys make sure the task and user
   are real.   
*/
CREATE TABLE IF NOT EXISTS task_comments (
	id INT AUTO_INCREMENT PRIMARY KEY,
	task_id INT NOT NULL,
	user_id INT NOT NULL,
	comment TEXT NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
