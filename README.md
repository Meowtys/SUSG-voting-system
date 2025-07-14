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

1. **Install XAMPP**  
   Download and install [XAMPP](https://www.apachefriends.org/index.html) if not already installed.

2. **Clone or Download the Repository**  
   Place the project folder inside your `htdocs` directory (usually found in `C:/xampp/htdocs`):
3. **Import the Database**

- Open **phpMyAdmin** (`http://localhost/phpmyadmin`)
- Create a new database (e.g., `susg_voting_db`)
- Import the provided `.sql` file (usually found in the `database/` folder)

4. **Configure Database Connection**

- Open the project folder
- Edit the `config.php` or equivalent DB connection file:
  ```php
  $host = "localhost";
  $username = "root";
  $password = "";
  $database = "susg_voting_db";
  ```
5. **Start the Server**

- Open **XAMPP Control Panel**
- Start **Apache** and **MySQL**
- Go to your browser and visit:
  ```
  http://localhost/susg-voting-system/
  ```
