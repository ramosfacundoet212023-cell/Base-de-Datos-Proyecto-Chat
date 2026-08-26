CREATE TABLE Usuario (
    ID_Us INT AUTO_INCREMENT,
    Nombre_y_Apellido VARCHAR(150) NOT NULL,
    Mail VARCHAR(100) NOT NULL,
    Telefono VARCHAR(20) NOT NULL,
    Contrasena VARCHAR(255) NOT NULL,
    PRIMARY KEY (ID_Us)
);

CREATE TABLE Moderador (
    ID_AGBD INT AUTO_INCREMENT,
    Nombre_y_Apellido VARCHAR(150) NOT NULL,
    Mail VARCHAR(100) NOT NULL,
    Telefono VARCHAR(20) NOT NULL,
    Contrasena VARCHAR(255) NOT NULL,
    PRIMARY KEY (ID_AGBD)
);

-- Nueva Tabla para Aulas
CREATE TABLE Aula (
    ID_Aula INT AUTO_INCREMENT,
    Nombre_Aula VARCHAR(100) NOT NULL,
    Clave VARCHAR(255) NOT NULL,
    PRIMARY KEY (ID_Aula)
);

-- Tabla Chats corregida con referencia a ID_Aula
CREATE TABLE Chats (
    ID_Chat INT AUTO_INCREMENT,
    ID_Us INT NOT NULL,
    ID_AGBD INT NULL, -- Ahora puede ser NULL si no hay moderador asignado aún
    ID_Aula INT NOT NULL,
    Mensajes TEXT NOT NULL,
    Miembros INT DEFAULT 1,
    PRIMARY KEY (ID_Chat),
    FOREIGN KEY (ID_Us) REFERENCES Usuario(ID_Us),
    FOREIGN KEY (ID_AGBD) REFERENCES Moderador(ID_AGBD),
    FOREIGN KEY (ID_Aula) REFERENCES Aula(ID_Aula)
);

-- Inserts de prueba con contraseñas encriptadas con password_hash()
-- Contraseña alumno: 'alumno123'
INSERT INTO Usuario (Nombre_y_Apellido, Mail, Telefono, Contrasena) 
VALUES ('Ramos Facundo', 'framos@institucion.edu', '11-2345-6789', '$2y$10$e.w26S3cZJ/32gE21kOveOmsA16f3S0L201G27/Jk5B.5l1b8lRaq');

-- Contraseña moderador: 'admin123'
INSERT INTO Moderador (Nombre_y_Apellido, Mail, Telefono, Contrasena) 
VALUES ('Esteban Contreras', 'econtreras@institucion.edu', '11-9876-5432', '$2y$10$wE1MvO3/R6a7U/Zl79kge.87fXW/sFq/O7E/X1g5m71Bf17bS7Oqa');

-- Clave del aula: 'aula123'
INSERT INTO Aula (Nombre_Aula, Clave) 
VALUES ('Matemática I - Turno Mañana', '$2y$10$X8O57M3h391.n1V76Z9s3eB.M2w.N8Q98v/7V3o1e93m8z5V4A1v2');

INSERT INTO Usuario (Nombre_y_Apellido, Mail, Telefono, Contrasena) 
VALUES ('Tu Nombre', 'tuemail@gmail.com', '11-0000-0000', 'google_auth');