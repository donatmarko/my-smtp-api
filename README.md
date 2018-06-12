# my-smtp-api
> A simple SMTP relay API for my own-developed projects where I can't use SMTP directly due to server/firewall limitations.

## What is it?
Basically this API sends the email which has been received via POST request.
Nowadays, when you subscribe for a VPS (virtual private server) at any companies, you will most likely face the problem that all SMTP-related ports are blocked to avoid their IP being blacklisted, and the provider is only willing to disengage port block after a thorough ID-check.
When it happened to me, I already had a shared hosting subscription at an other provider (where my mailing and some subdomains were), so I decided to get the two services closer with an API.

## SaaS?
I don't provide API as **SaaS** (*software as a service*) mainly due to privacy reasons. Next to that I cannot fully guarantee that the API is fully secure and complies all security requirements, and also I don't want to be blacklisted if somebody sends SPAM through my API --- although x mail/minute limitation could be easily implemented.
I recommend to run the API on your own-maintained server or shared hosting.

## Requirements
- PHP 5.6+
- one MySQL database
- composer
 
## Usage
Check the wiki (soon).

## Support?
Create a new issue at GitHub and I will do my best with.

## Warranty?
No.
