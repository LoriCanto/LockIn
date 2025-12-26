-- STORED PROCEDURE PER L CREAZIONE SQL DI LOCKERS





--azzerare auto increment
ALTER TABLE locker AUTO_INCREMENT = 1;



DELIMITER //

CREATE PROCEDURE LockerInsert(IN numero_righe INT, IN anno varchar(10) , IN gruppo varchar(5), IN tipo varchar(10))
BEGIN
    -- Dichiarazione della variabile contatore locale
    DECLARE contatore INT DEFAULT 1;
    -- Il ciclo ora continua fino al valore passato come parametro
    WHILE contatore <= numero_righe DO
        INSERT INTO locker (anno, codice, gruppo, tipo) 
        VALUES (anno, CONCAT(gruppo, contatore), gruppo, tipo);
        -- Incremento del contatore
        SET contatore = contatore + 1;
    END WHILE;
END //

-- ESEMPIO DI UTILIZZO DELLA PROCEDURA
CALL LockerInsert(10, '2023-2024', 'A', 'Piccolo');

-- Rimuovi Armadietto
CREATE PROCEDURE LockerRemove(IN lockerID INT)
BEGIN
    DELETE FROM locker
    WHERE id = lockerID;
END //
-- Prenota Armadietto
CREATE PROCEDURE LockerLock(IN lockerID INT, IN utenteID INT)
BEGIN
    UPDATE locker
    SET utente = utenteID, data_prenotazione = NOW()
    WHERE id = lockerID;
END //
-- Rilascia Armadietto
CREATE PROCEDURE LockerUnlock(IN lockerID INT)
BEGIN
    UPDATE locker
    SET utente = null, data_prenotazione = null
    WHERE id = lockerID;
END //
DELIMITER ;