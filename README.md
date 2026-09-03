# TYPO3 extension `impersonate`

[![TYPO3 14](https://img.shields.io/badge/TYPO3-14-orange.svg)](https://get.typo3.org/version/14)
[![Latest Stable Version](https://poser.pugx.org/portrino/impersonate/v/stable)](https://packagist.org/packages/portrino/impersonate)
[![Total Downloads](https://poser.pugx.org/portrino/impersonate/downloads)](https://packagist.org/packages/portrino/impersonate)
[![Monthly Downloads](https://poser.pugx.org/portrino/impersonate/d/monthly)](https://packagist.org/packages/portrino/impersonate)
[![License](https://poser.pugx.org/portrino/impersonate/license)](https://packagist.org/packages/portrino/impersonate)
[![CI](https://github.com/portrino/impersonate/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/portrino/impersonate/actions/workflows/ci.yml)

> Impersonate frontend users from inside the TYPO3 backend.

## Features

The **Impersonate** extension gives backend users with administrator privileges the possibility to authenticate as any specific
frontend user in the frontend with just a single click from inside the backend. This **does not include** default
backend users.

And remember: *With great power comes great responsibility*. The purpose of this extension is mainly to allow for a tech
support login as a specific user and see potential problems and bugs from the perspective of the user as well as doing
tech support actions while impersonating the specified user account.

![Screenshot](/Resources/Public/Screenshots/impersonate.png)

---

## Setup

### 1. Installation

#### Installation with Composer

The **recommended** way to install the extension is using [composer](https://getcomposer.org/).

Run the following command within your Composer based TYPO3 project:

`composer require portrino/impersonate`.

#### Installation with TER

Open the TYPO3 Extension Manager, search for `impersonate` and install the extension.

### 2. Configuration (optional)

- Go to the `Site Management/Sites` module in the backend and include the `Impersonate` site set as dependency to your 
  site configuration.
- Afterward open the `Site Management/Settings` module and set the id of the target page to redirect an admin to when 
  impersonating a frontend user via the backend:
  - `tx_impersonate.loginRedirectPid`
    - If not set, the user will be redirected to the root page of the site.
    - **Important:** Impersonation needs an active backend user session for the target page hostname!
      - The target page has to have the same hostname as the backend, otherwise the impersonation will fail due 
        to backend user session checks. This is especially important if you have multiple sites with different hostnames 
        in your TYPO3 instance.

- By default, only backend admin users are allowed to impersonate frontend users. You can change this behavior by
  setting the following user TSconfig value for the respective backend user or backend user group:
    - `tx_impersonate.enable = 1` (set to `0` to disable again)

### 3. Usage

- Go to the list module as a backend user (with administrator privileges), open a page / sysfolder with frontend user
  records and click the "Impersonate user" button.
- Congratulations! You are now logged in as the chosen frontend user.

---

## Compatibility

| Impersonate | TYPO3     | PHP       | Support / Development                |
|-------------|-----------|-----------|--------------------------------------|
| 6.x         | 14.3      | 8.3 - 8.5 | features, bugfixes, security updates |
| 5.x         | 13.4      | 8.2 - 8.4 | features, bugfixes, security updates |
| 4.x         | 13.4      | 8.2 - 8.4 | bugfixes, security updates           |
| 3.x         | 12.4      | 8.1 - 8.3 | none                                 |
| 2.x         | 11.5      | 7.4 - 8.3 | none                                 |
| 1.1.x       | 10.4      | 7.0 - 7.4 | none                                 |
| 1.0.x       | 8.7 - 9.5 | 7.0 - 7.4 | none                                 |

**Please note that the namespace has changed to "portrino" since version 5.**
**If you extended impersonate with your own code, please use the new namespace also.**

---

## Authors

* See the list of [contributors](https://github.com/IndyIndyIndy/impersonate/graphs/contributors) who participated in this project.
