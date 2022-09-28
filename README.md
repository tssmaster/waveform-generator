### Waveform Generator

Requirements:
- PHP >= 7.5.12
- Apache or Nginx web server

Installation:
- Clone repository, it will create folder 'waveform-generator'
- Configure your Apache/Nginx server to open custom URL address for example 'http://waveform-generator.dev' - this must serve the folder /waveform-generator/public
- In project folder open Bash console and type
    
    **composer install**

- To run the project you need to open the URL in your browser

    **http://waveform-generator.dev**
    
- To run unit tests open Bash console and type

    **php bin/phpunit tests**
