# Zine
A simple, single-file script to quickly create posts

Zine requires no database and doesn't include a lot of features. It is  
specifically meant for creating content for a single user.

Settings are managed via the **/manage** route which also allows changing  
the default password. Create new posts via **/new** which requires the  
author to use **/login**.

The default password is password

The storage folder requires only read/write permissions to be set. It is  
strongly recommended that no other permissions are allowed.

## Features  

Zine requires minimal resources and no database. Backing up is a matter  
of copying the storage directory. There are no special utilities or  
functionality required besides what already comes bundled with PHP.  

Changing basic site settings, including the password, is accomplished via  
the management panel. No manual configuration file editing required.  

HTML is filtered of potentially harmful tags, however embedding videos  
to YouTube or Vimeo is supported via shortcodes.
```
E.G. For Youtube: 

[youtube https://www.youtube.com/watch?v=RJ0ULhVKwEI]
or
[youtube https://youtu.be/RJ0ULhVKwEI]
or
[youtube RJ0ULhVKwEI]

For Vimeo:

[vimeo https://vimeo.com/113315619]
or
[vimeo 113315619]
```

A simple subset of [Markdown](https://daringfireball.net/projects/markdown/) syntax is also supported

## Requirements  

  - PHP 5.6 or greater ( 7+ preferable )  
  - Web server that allows friendly URLs ( Apache .htaccess provided )  
  - Read and write permissions to the storage folder


## Purpose

It is becoming annoying to impossible to setup a web presence without  
sharing an undue amount of private information about one's self or  
without sophisticated tech expertise. It is hoped that Zine is a step  
toward reversing that trend by making it as simple as is possible to  
make a website that can be updated dynamically. 

The single user functionality is a deliberate attempt to draw focus to  
personal speech.

Speaking is as fundamental to the human condition as breathing.  
To force silence upon someone else is to force death.

Knowledge is now funneled into generic timelines, curated by behavior,  
by gender, by connections, anything but conscious choice.  

Stop being the product. 

Start breathing again. Reclaim your right to live by speaking. Create  
websites that you run and control, away from the walled gardens that  
tease you with a text box while snooping on your identity and browsing  
habits. Away from those silos which silence you for your volume, or  
brashness, or for having the wrong name, or for offending the wrong  
people. Print works at home or at your local library - books, pamphlets,  
papers - and give them away at your local train station, bus stop,  
farmers' market. Reclaim the commons.

Make it beautiful, ugly, sublime, horrendous, funny, grotesque or some  
combination thereof.

Write in long-form, short-form, cuneiform, it doesn't matter.

Get away from the arbiters of taste, of what constitutes art or even  
which of the forms of expression qualify as a forms of expression.

Above all, feel comfortable expressing again.

Zine is intended to bring the focus of sharing to one's self as a form  
of venting, self expression or anything that isn't merely a siphon point  
for the gatekeepers of social media.
