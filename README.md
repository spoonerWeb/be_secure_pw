# TYPO3 extension be_secure_pw

[![Latest Stable Version](https://poser.pugx.org/spooner-web/be_secure_pw/v/stable)](https://packagist.org/packages/spooner-web/be_secure_pw) [![Total Downloads](https://poser.pugx.org/spooner-web/be_secure_pw/downloads)](https://packagist.org/packages/spooner-web/be_secure_pw) [![Monthly
Downloads](https://poser.pugx.org/spooner-web/be_secure_pw/d/monthly)](https://packagist.org/packages/spooner-web/be_secure_pw) [![Daily Downloads](https://poser.pugx.org/spooner-web/be_secure_pw/d/daily)](https://packagist.org/packages/spooner-web/be_secure_pw)

## tldr

This extension keeps the editor's password safe and secure

## What does it do?

This extension

* can set different patterns for the BE user's password
    * capital chars
    * lower chars
    * digits
    * special chars
    * and set the number of patterns a password must have
* can set a minimum length of a password
* is checking the password in setup module and in BE user record
* is able to set a period of time a password becomes expired and a BE user needs to change it
* is able to force this password change
    * a BE user with an expired password has only access to the setup module
* is able to lookup in the pawned password database if the password is found in data breaches

## How to install?

### composer

`composer require spooner-web/be_secure_pw`

### TYPO3 Extension Manager

Search for `be_secure_pw` and install it via the EM interface

### ZIP upload in EM

Go to the [TER page](https://extensions.typo3.org/extension/be_secure_pw) and download the ZIP file. After that, upload it in your
TYPO3 Backend in the EM interface.

## How to contribute?

* [Send issues](https://git.spooner.io/spooner/be_secure_pw/issues) (bugs, suggestions, features)
* [Donate via PayPal](https://paypal.me/Tomalo/50)
* Send code and create Merge Requests in [GitLab](https://git.spooner.io/spooner/be_secure_pw)

## Credits

* Lightwerk GmbH and its customer who wants such a solution for password
* Torben Hansen for the code of PawnedPasswordService
* All other contributors
