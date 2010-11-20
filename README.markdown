This plugin integrates [PlantUML](http://plantuml.sourceforge.net) into the [DukuWiki](http://www.dokuwiki.org) wiki engine.
It allows to generate UML graph images from simple description text block.

# Features
* Create any UML graph supported by PlantUML.
* Generated images are PNGs.
* Generated images are saved into the media folder of DokuWiki: *path/to/dokuwiki/data/media/plantuml* and regenerated only when a change occurs into the graph description.
* Toolbar button (optional)
* Control the display witdh and height.

# Requirements
* [plantuml.jar](http://plantuml.sourceforge.net/download.html)
* [Graphwiz](http://www.graphviz.org)
* [Java runtime](http://www.java.com/download)

# Installation
* If Graphwiz and a Java runtime are not installed on your system, install them first.
* Download the dokuwiki_plantuml archive.
  * In the plugin directory of DokuWiki installation, which should be *path/to/dokuwiki/lib/plugins*, create a folder *plantuml* and extract the archive's content into it (more on plugin installation [here](http://www.dokuwiki.org/plugin_installation_instructions)
  * Make sure both the new directory and the files therein are readable by the web-server.
* If you don't already use PlantUML, download plantuml.jar and put it at the root of the plantuml plugin directory.
* In the case you already have PlantUML on your system, you'll be able to specify the path to plantuml.jar file by using the *Administration/Manage Plugins* Dokuwiki's page.

# Sample
This block will generate a sequence diagram:  
@startuml  
Alice -> Bob: Authentication Request  
Bob --> Alice: Authentication Response  
  
Alice -> Bob: Another authentication Request  
Alice <-- Bob: another authentication Response  
@enduml  

# Control display size
Following the start tag *@startuml*, you can specify the witdh and/or height of the image in your wiki page, like this:  
@startuml w=X h=Y

# Image Title
By default, html img title attribute is set to "PlantUML Graph". You can specify your own graph title like this:  
@startuml  
title=This will be the title  
...  
*graph description*  
...  
@enduml  
