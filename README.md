# PHP Email Collector (Raspberry Pi)

![PHP](https://img.shields.io/badge/PHP-v8.1-blue)
![HTML](https://img.shields.io/badge/HTML-CSS-orange)
![Raspberry Pi](https://img.shields.io/badge/RaspberryPi-Offline%20Server-green)

## About

This is an offline PHP web application designed to collect and manage parent email addresses during school events.

It runs entirely on a Raspberry Pi as a local server and provides a simple interface for parents to enter their contact information. The admin panel allows secure CSV export and management of all entries.

Created as part of an FSJ IT project.

## Features

- Runs on Raspberry Pi via local Wi-Fi (offline mode)  
- Collects parent emails securely  
- Prevents duplicate entries  
- Verification option via student birth date  
- Admin dashboard to upload/download/delete data

## Tech Stack

- **PHP** – Backend logic
- **HTML & CSS** – Frontend
- **CSV** – Data storage
- **Raspberry Pi** – Local server
- **.htaccess** – Admin access protection
- **Apache / WLAN setup**

## Installation

1. Clone the repo
   ```sh
   git clone https://github.com/yannic-toulouse/emailCollector.git
   ```
2. Place on Raspberry Pi with Apache/PHP enabled
3. Configure your WLAN and connect devices
4. Set .htaccess credentials for secure/ directory
5. Visit IP address in browser

## License

MIT License © Yannic Toulouse
