CREATE TABLE Persons (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    username VARCHAR(30),
    unique_id VARCHAR(20),
    position SMALLINT,
    status VARCHAR(10)
);

CREATE TABLE Requests (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    unit SMALLINT,
    created_by BIGINT,
    title VARCHAR(200),
    description VARCHAR(300),
    req_status SMALLINT,
    is_closed SMALLINT,
    rate SMALLINT,
    reason VARCHAR(300),
    register_date DATE, 
    reject_date DATE,
    accept_date DATE,
    predict_date VARCHAR(15),
    register_time TIME,
    reject_time TIME,
    rejector BIGINT,
    accept_time TIME,
    done_date Date,
    done_time TIME
);