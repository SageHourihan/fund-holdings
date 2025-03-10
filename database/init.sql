-- Create database
CREATE DATABASE IF NOT EXISTS fund_holdings;
USE fund_holdings;

-- Create table for fund info
CREATE TABLE funds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticker VARCHAR(20) UNIQUE NOT NULL, -- Fund ticker symbol
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create table for fund holdings info
CREATE TABLE fund_holdings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fund_id INT NOT NULL,
    stock_ticker VARCHAR(20) NOT NULL, -- Stock ticker
    stock_name VARCHAR(255) NOT NULL, -- Stock name
    percent_weight DECIMAL(8,4) NOT NULL, -- % of fund's total assets
    shares BIGINT NOT NULL, -- Number of shares held
    last_updated DATE NOT NULL, -- Date the data was last refreshed
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (fund_id) REFERENCES funds(id) ON DELETE CASCADE,
    UNIQUE KEY (fund_id, stock_ticker) -- Ensures a stock appears once per fund
);

