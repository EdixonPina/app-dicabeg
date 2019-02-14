CREATE TABLE "users_data" (
 "user_id" VARCHAR(36),
 "username" VARCHAR(20) NOT NULL,
 "names" VARCHAR(20),
 "lastnames" VARCHAR(20),
 "age" INTEGER,
 "image" VARCHAR,
 "phone" VARCHAR(20),
 "points" INTEGER,
 "movile_data" INTEGER,
 "update_date" TIMESTAMP,

 PRIMARY KEY (user_id),
 CONSTRAINT users_data_users_id_FK FOREIGN KEY(user_id) REFERENCES users_accounts(user_id)
);

CREATE INDEX users_data_username_UQ ON users_data(username);
