## TODO: Implement password hashing (pre-launch blocker)

**Status:** Not started. Currently storing/comparing plaintext passwords.
**Priority:** Must be done before any real election/live deployment. 
Not urgent during active feature development, but cannot ship without this.

### What needs to change
- [ ] Add `password_hash($password, PASSWORD_DEFAULT)` wherever accounts 
      are created (student/voter registration, Comelec account creation, 
      bulk student upload via `process_bulk_upload.php`)
- [ ] Replace plaintext password comparison in `technical/auth/user-session.php` 
      with `password_verify($inputPassword, $storedHash)`
- [ ] Migrate existing plaintext passwords in the `students`/`users` table — 
      either force a password reset for all accounts, or run a one-time 
      migration script that hashes existing values (only safe if you still 
      have the plaintext to hash; if not, must force reset)
- [ ] Double check `sql/votesusg.sql` / DB schema — password column width 
      needs to support bcrypt hash length (60 chars minimum, use VARCHAR(255) to be safe)
- [ ] Re-check all login-related files for any remaining plaintext password 
      logging or debug output before this goes live

### Why this matters
Students likely reuse passwords across services. A DB leak while passwords 
are in plaintext exposes real people beyond just this app.