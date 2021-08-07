

/**
 * Polcies
 */
CREATE TABLE armor_policies (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    name VARCHAR(255) NOT NULL, 
    policy LONGTEXT NOT NULL
) engine=InnoDB;

/**
 * Users
 */

CREATE TABLE armor_users (
    uuid VARCHAR(30) NOT NULL PRIMARY KEY, 
    is_active BOOLEAN NOT NULL DEFAULT true,
    is_pending BOOLEAN NOT NULL DEFAULT false, 
    is_frozen BOOLEAN NOT NULL DEFAULT false, 
    is_deleted BOOLEAN NOT NULL DEFAULT false,  
    type VARCHAR(20) NOT NULL DEFAULT 'user', 
    username VARCHAR(80) NOT NULL DEFAULT '', 
    password VARCHAR(80) NOT NULL DEFAULT '', 
    email VARCHAR(80) NOT NULL DEFAULT '', 
    phone VARCHAR(20) NOT NULL DEFAULT '', 
    reg_ip VARCHAR(60) NOT NULL DEFAULT '', 
    reg_user_agent VARCHAR(60) NOT NULL DEFAULT '', 
    reg_country VARCHAR(2) NOT NULL DEFAULT '', 
    reg_province_iso_code VARCHAR(5) NOT NULL DEFAULT '', 
    reg_province_name VARCHAR(80) NOT NULL DEFAULT '', 
    reg_city VARCHAR(80) NOT NULL DEFAULT '', 
    reg_latitude DECIMAL(8,4) NOT NULL DEFAULT 0, 
    reg_longitude DECIMAL(8,4) NOT NULL DEFAULT 0, 
    email_verified BOOLEAN NOT NULL DEFAULT false, 
    phone_verified BOOLEAN NOT NULL DEFAULT false, 
    two_factor_type VARCHAR(10) NOT NULL DEFAULT 'none',
    two_factor_frequency VARCHAR(10) NOT NULL DEFAULT 'none',  
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    updated_at TIMESTAMP, 
    unfreeze_at TIMESTAMP
) engine=InnoDB;

CREATE TABLE armor_users_devices (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    uuid VARCHAR(30) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT true,  
    type ENUM('pc','ios','android') NOT NULL DEFAULT 'pc', 
    device_id VARCHAR(160) NOT NULL, 
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    updated_at TIMESTAMP,  
    FOREIGN KEY (uuid) REFERENCES armor_users (uuid) ON DELETE CASCADE
) engine=InnoDB;

CREATE TABLE armor_users_ipallow (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    uuid VARCHAR(30) NOT NULL, 
    ip_address VARCHAR(60) NOT NULL, 
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    updated_at TIMESTAMP, 
    FOREIGN KEY (uuid) REFERENCES armor_users (uuid) ON DELETE CASCADE
) engine=InnoDB;

CREATE TABLE armor_users_log (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    uuid VARCHAR(30) NOT NULL, 
    action VARCHAR(20) NOT NULL, 
    ip_address VARCHAR(60) NOT NULL, 
    user_agent VARCHAR(60) NOT NULL, 
    old_item VARCHAR(80) NOT NULL, 
    new_item VARCHAR(80) NOT NULL, 
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (uuid) REFERENCES armor_users (uuid) ON DELETE CASCADE
) engine=InnoDB;

CREATE TABLE armor_pending_updates (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    uuid VARCHAR(30) NOT NULL,
    action VARCHAR(20) NOT NULL DEFAULT 'password',  
    data_id INT NOT NULL, 
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (uuid) REFERENCES armor_users (uuid) ON DELETE CASCADE
) engine=InnoDB;


/**
 * Login history
 */

CREATE TABLE armor_history_logins (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    is_valid BOOLEAN NOT NULL DEFAULT true, 
    is_auto_login BOOLEAN NOT NULL DEFAULT false, 
    uuid VARCHAR(30) NOT NULL, 
    ip_address VARCHAR(60) NOT NULL, 
    user_agent VARCHAR(255) NOT NULL, 
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    logout_at TIMESTAMP, 
    FOREIGN KEY (uuid) REFERENCES armor_users (uuid) ON DELETE CASCADE
) engine=InnoDB;

CREATE TABLE armor_history_reqs (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    history_id INT NOT NULL, 
    method VARCHAR(10) NOT NULL DEFAULT 'GET', 
    uri VARCHAR(120) NOT NULL,
    query_string TEXT NOT NULL, 
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (history_id) REFERENCES armor_history_logins (id) ON DELETE CASCADE
) engine=InnoDB;

/**
 * Encryption
 */

CREATE TABLE armor_keys (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    is_pending_sign BOOLEAN NOT NULL DEFAULT false, 
    uuid VARCHAR(30) NOT NULL, 
    master_id INT NOT NULL DEFAULT 0, 
    password_id INT NOT NULL DEFAULT 0, 
    algo VARCHAR(5) NOT NULL DEFAULT 'rsa', 
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    updated_at TIMESTAMP,
    fingerprint VARCHAR(130) NOT NULL DEFAULT '',
    iv VARCHAR(24) NOT NULL DEFAULT '', 
    public_key TEXT, 
    private_key TEXT, 
    certificate TEXT,  
    FOREIGN KEY (uuid) REFERENCES armor_users (uuid) ON DELETE CASCADE
) engine=InnoDB;

CREATE TABLE armor_data (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    encdata LONGTEXT
) engine=InnoDB;

CREATE TABLE armor_data_index (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    data_id INT NOT NULL, 
    uuid VARCHAR(30) NOT NULL,
    keydata TEXT,   
    FOREIGN KEY (data_id) REFERENCES armor_data (id) ON DELETE CASCADE, 
    FOREIGN KEY (uuid) REFERENCES armor_users (uuid) ON DELETE CASCADE
) engine=InnoDB;

CREATE VIEW armor_encdata AS 
    SELECT d.id data_id, d.encdata encdata, i.uuid uuid, i.keydata keydata, d.created_at created_at 
    FROM armor_data d LEFT JOIN armor_data_index i ON 
    d.id = i.data_id;
 




