# type 1 query
INSERT INTO usr(email, register_date, verify, tok, passwrd)
VALUES
("hui@hui.com", NOW(), 0, "xxxxxxxxhui", "123456"),
("hui2@hui.com", NOW(), 0, "xxxxxxxhui2", "123456"),
("hui3@hui.com", NOW(), 0, "xxxxxxxhui3", "123456")


INSERT INTO rest_passwrd(email, when_, location_ip, temp_token, rest)
VALUES
("hui@hui.com", NOW(), "127.0.0.1", "000000", "0")


UPDATE
    usr T1,
    rest_passwrd T2
SET
    T1.passwrd = 'fwg43gw3g34g34', T2.rest = 1
WHERE
        T1.email = T2.email AND T2.email = "hui@hui.com" AND T2.rest = 0