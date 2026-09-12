# SUSG Voting System

The **SUSG Voting System** is a secure, transparent, and user-friendly digital platform designed for managing student elections at Silliman University. Built to ensure integrity, ease of access, and real-time vote tallying, this system supports authenticated voting, candidate management, and results reporting.

---

## 🧩 Features

- ✅ Secure student login 
- ✅ Vote once per election per student
- ✅ Admin panel for managing elections and candidates
- ✅ Real-time vote counting
- ✅ Audit logs for transparency

## 🗳️ How It Works

1. **Students Login**  
   Each student logs in using their Silliman-issued credentials.

2. **Election Opens**  
   Admin enables the voting window. Ballots become visible.

3. **Voting**  
   Students cast their votes securely. Vote is saved and cannot be changed.

4. **Vote Tallying**  
   Votes are automatically counted in real-time, with results visible to authorized users.

5. **Audit & Export**  
   Admin can export results and access detailed logs.

## ⚙️ Setup Instructions (Localhost)

1. **Place the project in XAMPP**
   Download and install [XAMPP](https://www.apachefriends.org/index.html) if not already installed.
   Place this project inside `C:/xampp/htdocs/susg-voting-system`.

2. **Build the frontend assets**
   Install [Node.js](https://nodejs.org/) 20 or newer, then run these commands from the project directory:
   ```sh
   npm install
   npm run build
   ```
   The build compiles Tailwind CSS for production and copies pinned third-party assets into `asset/`. Re-run `npm run build` after changing frontend markup or updating dependencies.

3. **Import the database**

- Open **phpMyAdmin** (`http://localhost/phpmyadmin`)
- Create a database named `votesusg`
- Import the provided `sql/votesusg.sql` file

4. **Check the database connection**

- The default local XAMPP settings used by `connect.php` are:
  ```text
  host: localhost
  database: votesusg
  username: root
  password: (empty)
  ```
- If your MySQL credentials differ, update `connect.php`.

5. **Start the server**

- Open **XAMPP Control Panel**
- Start **Apache** and **MySQL**
- Visit:
  ```
  http://localhost/susg-voting-system/
  ```
