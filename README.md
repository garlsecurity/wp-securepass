This is a wordpress plugin to autheticate aganst SecurePass service.
Initially written by Giuseppe Paterno' (gpaterno@gpaterno.com) on 2012.

Read the comments in the securepass.php and set the variables accordingly,
with specific regards to the $radius_secret that must match the one defined
on SecurePass.

This software is released under GPLv2.
Please note that this software comes with NO WARRANTIES!!!!!
Although is known to work, use it at YOUR OWN RISK.

Neither the author(s), SecurePass or GARL Sagl is responsible for this software.

This plug-in contains "Pure PHP radius class", a PHP class to connect to
RADIUS. This sofware is Copyright (c) 2008, SysCo systemes de communication sa


== Preface ==
A lot of web sites, even well known ones (newspapers, telcos, ...) adopts 
WordPress as their CMS. WordPress is a great platform, however it
can happen that password leaking or guessing might lead to unauthorized
access to the platform. A potential attacker can be therefore able to 
change articles, part of the web site and/or make the website unavailable,
with image and economic damages for a company or for a blogger.
This is even more true if your website is not SSL protected.

SecurePass is a SaaS service offering an easy and affordable solution
for One Time Passwords (OTP) and strong authentication in general. They 
offer 5 users for free included with their standard (=basic) account, which
is more than enough for standard blogs and web sites. Companies can purchase
additional users, if needed.

== Setup and configure SecurePass ==
If you don’t own already an account with SecurePass, you can sign-up for a new account here: http://www.secure-pass.net/open

Connect to the admin interface on https://admin.secure-pass.net 
and create a new device (basically a RADIUS client). 

In the admin interface, go to the "Device" section and add a new device. 
You will need to set the public IP Address of the server, a fully qualified 
domain name (FQDN), and the secret password for the radius authentication. 
It's ok if your web server is behind a firewall and/or NAT, ensure that
your server has rights to send (and receive) RADIUS authentication requests,
i.e. UDP port 1812.


== Setup and configure the WordPress Plugin ==
Simply copy this software's directory in the following WordPress location:
wp-content/plugins/

For production environments:
Go to the WP-SecurePass control panel and select RADIUS protocol.
Insert "radius.secure-pass.net" as a RADIUS host, this is SecurePass' global load balancer.
If you want to have a fine-granted control, you can specify one of the datacenters listed here:
http://support.secure-pass.net/wiki/index.php/Help:RADIUS

Insert the secret password as specified in the "Device" specified in the SecurePass
administration panel. 

WARNING!!! Before activating the plugin, create an user in wordpress that
matches a username in SecurePass and grant full administrative powers.
This because the admin user will be no longer checked locally. In case you 
won't be able to login anymore, a workaround is moving the securepass plugin 
directory to another directory name, ex: "mv securepass securepass.old".

== Setup for SecurePass Beta (aka Dreamliner) ==
With the SecurePass tools, create a new app, with readonly capabilities and possibly restricing IPs 
to the wordpress IP address or network.

Select the "RESTful APIs" protocol and insert the Application ID and Application Secret as released
by the SecurePass tools. The beta endpoint is https://beta.secure-pass.net.


== Further reading ==

* This plugin web site: 
  https://github.com/garlsecurity/wp-securepass

* SecurePass web site: 
  http://www.secure-pass.net/
