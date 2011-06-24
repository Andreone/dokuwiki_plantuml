This plugin integrates [PlantUML](http://plantuml.sourceforge.net) into the [DukuWiki](http://www.dokuwiki.org) wiki engine.
It allows to generate UML graph images from simple description text block.

# Features
* Create any UML graph supported by PlantUML.
* Generated images are PNGs.
* Generated images are cached and regenerated when needed.
* Toolbar button (optional)
* Control the display witdh, height and alignment.
* Works with the PlantUML webservice and/or a local installation.

# Requirements (for local PlantUML installation)
* [plantuml.jar](http://plantuml.sourceforge.net/download.html)
* [Java runtime](http://www.java.com/download)
* [Graphviz](http://www.graphviz.org) _You don't need this if you only want to generate sequence diagrams_

# Sample
This block describes a sequence diagram:

    <uml>
    Alice -> Bob: Authentication Request  
    Bob -> Alice: Authentication Response  
    </uml>

and results in:

![Sample](http://plantuml.sourceforge.net/img/sequence_img001.png)

# Control display size
Inside the start tag *`<uml>`*, you can specify the width and/or height of the image using one of the following ways:

    <uml w=100>
    <uml width=100>
    <uml w=80%>
    <uml width=80%>
    <uml 100x200>

# Image Title
By default, html img title attribute is set to "PlantUML Graph". You can specify your own graph title like this:

    <uml title="This will be the title">
    <uml t=Diagram>

Note: Multiple words need to be placed in double quotes.


# Contributors
* [Willi Schönborn](https://github.com/whiskeysierra): rewrite of the syntax plugin with many additional features
