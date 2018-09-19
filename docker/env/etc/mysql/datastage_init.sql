USE datastage;


CREATE USER 'user1'@'%' IDENTIFIED WITH mysql_native_password  BY 'newpassword';


GRANT ALL PRIVILEGES ON datastage.* TO 'user1'@'%'; 

FLUSH PRIVILEGES;