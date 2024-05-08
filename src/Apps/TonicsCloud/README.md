# TonicsCloud

TonicsCloud 🌧️ is a Domain specific project under the TonicsCMS.

It is an open source system container server management tool that leverages the flexibility of system containers such as Incus and VPS API for provisioning the server and managing DNS.

## Why I Built TonicsCloud

…so I can host multiple application/projects on a server in a truly isolated way.

There are several ways this can be done but they are mostly insecure in the sense that, a vulnerable app or project in the server might affect other application since they are not entirely isolated from each other anyway.

Another way is using an application container such as docker, while this is a good idea, application container doesn’t fit my use case.

I want that touch of a traditional server and that is where system container comes in. It gives you the feel of a traditional server with the added benefits of containerization and the ability to run multiple processes at the same time.

In essence, what I am looking for is container isolation in a sever with no too many limitation as to what I can do with my container.

For small agency, you can pack multiple website on one server in a truly isolated way and for personal use, you can host projects on a server without the need of provisioning new servers for each project.

## Target Audience

* For hosting personal projects
* Web Agencies that want to manage hosting for their clients, there is an option to charge clients directly
* Developers
* For pet projects
* and anyone that would love to host multiple apps on one server

## How Does Hosting Works in TonicsCloud

Everything you do lives in your server, TonicsCloud support multiple VPS Providers (currently Linode & UpCloud), so, all you have to do is plug in the API credentials from your preferred Provider and you are good to go.

TonicsCloud uses a system container to isolate one container from one another.

Once your server is provisioned from the TonicsCloud interface, you have the luxury to host multiple things (containers) in your server without any interference whatsoever from other container on the server.

Here are an example of things you can do on just one server:

1. Website Hosting
2. Mail Server
3. Database Server
4. Application Hosting
5. Development Environment
6. Proxy Server
7. and whatever you can do on a typical Linux distro

There is no limit to what you can have in your containers.

## Installation

There are several ways to install TonicsCloud, if you know your way around the command line, you can install TonicsCloud manually by following the instructions here: [Tonics » Getting Started (Overview and Installation)](https://tonics.app/posts/4823863fd7b5f88c/getting-started#installation).

The faster way of installing TonicsCloud is by supporting me and trying it here: [https://cloud.tonics.app/customer/](https://cloud.tonics.app/customer/signup)[signup](https://cloud.tonics.app/customer/register)

Building this project cost money and effort, I have been working on the TonicsCMS itself since 2021, and I started TonicsCloud last year, if you use the link above to signup, you are in essence supporting the project, and would be use to fix bugs and new features, thanks 🙏🏾

## Documentation

- [https://tonics.app/posts/78a5ff202fd027ac/tonicscloud-documentation](https://tonics.app/posts/78a5ff202fd027ac/tonicscloud-documentation)