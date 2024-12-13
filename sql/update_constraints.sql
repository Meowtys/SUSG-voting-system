-- Update students table to allow NULL election_id
ALTER TABLE `students` MODIFY `election_id` int(11) NULL;

-- Drop and recreate the foreign key with less restrictive constraints
ALTER TABLE `students` DROP FOREIGN KEY IF EXISTS `students_ibfk_2`;
ALTER TABLE `students` ADD CONSTRAINT `students_ibfk_2` 
    FOREIGN KEY (`election_id`) 
    REFERENCES `elections` (`election_id`) 
    ON DELETE SET NULL;
