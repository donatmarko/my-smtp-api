# my-smtp-api
> A simple SMTP relay API for my projects where I can't use SMTP directly because of server/firewall limitations.

## What is it?
Basically this API sends the email that has been received via POST request.
Nowadays, when you subscribe for a VPS (virtual private server) at the rest of the companies, you will most likely face the problem that all SMTP-related ports are blocked to avoid their IP getting blacklisted, and the provider is only willing to disengage port block after a thorough ID-check.
When it happened to me, I've already had a shared hosting subscription at an other provider (where my mailing and a few subdomains were at), so I decided to get the two services closer through an API.

## SaaS?
I don't provide API as **SaaS** (*software as a service*) mainly due to privacy reasons. Next to that I can not fully guarantee that the API is fully secure and complies all security requirements, also I don't want to get my IPs blacklisted if anyone sends SPAM through my API - although x mail/minute limitation could be easily implemented.
I recommend to run the API on your own server or shared hosting.

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
