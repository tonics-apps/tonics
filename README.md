# TonicsCMS

<!-- TOC -->
* [TonicsCMS](#tonicscms)
  * [About Tonics](#about-tonics)
  * [Tech Specification](#tech-specification)
  * [Installation](#installation)
  * [Documentation](#documentation)
<!-- TOC -->

![](https://tonics.app/serve_file_path_987654321/36d4b706389737e7aebd78f82bb9daa6e13de982e6b829f66f0443ed831a37dc?render)

Tonics CMS is an open-source versatile and flexible content management system that is built with a highly modular, and adaptable architecture.

Its unique feature is its event-driven approach, where modules communicate with each other through events, making it easy to develop and customize websites.

Little Sneak-Peak:

https://github.com/tonics-apps/tonics/assets/37757164/f08ed880-67a3-4286-8341-4a3fef7c5779

https://github.com/tonics-apps/tonics/assets/37757164/8611572d-910d-4a33-a0a8-930021ec807b

## About Tonics

Tonics CMS is born out of frustration with the limitations of traditional CMS platforms that force users to work within a rigid framework, while most CMS platforms are pluggable to a certain extent, there is no option to unplug features of the Core CMS.

In Tonics, you only use what you need while ensuring your website is tailored to your specific requirements, at the heart of Tonics Core System is an event-driven architecture, modules or apps communicate with each other through events, making it easy to hook into events, this makes Tonics an ideal choice for developers who want to build custom websites quickly and efficiently.

## Tech Specification

Tonics is a self-hosted PHP application that is built from the ground up with no framework, meaning, it is a vanilla PHP application. It works with PHP8.1+ and currently only support MariaDB10.6+

The following are the required PHP extension:

* Zip
* Intl
* PDO
* FileInfo
* BCMath
* Multibyte String (mbstring)
* APCU
* GMP
* CURL
* GD
* Pcntl
* POSIX
* Zlib

## Installation

There are several ways to install TonicsCMS, if you know your way around the command line, you can install Tonics manually by following the instructions here: [Tonics » Getting Started (Overview and Installation)](https://tonics.app/posts/4823863fd7b5f88c/getting-started#installation).

The faster way of installing Tonics is by supporting me and trying it here: [https://cloud.tonics.app/customer/](https://cloud.tonics.app/customer/signup)[signup](https://cloud.tonics.app/customer/register)

Building this project cost money and effort, I have been working on the TonicsCMS itself since 2021, if you use the link above to signup, you are in essence supporting the project, and would be use to fix bugs and new features, thanks 🙏🏾

## Documentation

Tonics is domain specific so depending on the Modules and App you are using, there are several documentation:

* [General Documentation](https://tonics.app/posts/6e2979da2737c415/user-documentation)
* [TonicsCloud Documentation](https://tonics.app/posts/78a5ff202fd027ac/tonicscloud-documentation)
