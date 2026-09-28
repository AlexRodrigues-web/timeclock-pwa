USE auditor_app;

ALTER TABLE work_days
    ADD COLUMN reopened_at DATETIME NULL,
    ADD COLUMN reopened_count INT NOT NULL DEFAULT 0;

ALTER TABLE time_entries
    ADD COLUMN updated_by_user_id INT NULL,
    ADD COLUMN deleted_at DATETIME NULL;
