USE auditor_app;

INSERT INTO users (name, email, password_hash)
VALUES ('Auditor', 'admin@local.test', '$2y$10$wH1Pygx8fXW1q4Q5M2Q6IuKj5f0G9Q0C8Wh0VqA8xv1mM8n7yF2e2')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO audit_types (name)
VALUES ('Gama'), ('Satisfação ao Cliente')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO stores (brand, name, city, district) VALUES
('Continente', 'Continente GaiaShopping', 'Vila Nova de Gaia', 'Porto'),
('Continente Modelo', 'Continente Modelo Trofa', 'Trofa', 'Porto'),
('Continente Bom Dia', 'Continente Bom Dia Maia Jardim', 'Maia', 'Porto'),
('Continente', 'Continente Matosinhos Sul', 'Matosinhos', 'Porto')
ON DUPLICATE KEY UPDATE name = VALUES(name);
