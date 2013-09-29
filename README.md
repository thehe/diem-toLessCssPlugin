toLessCssPlugin
=======================

## Dependencies ##
Install LESS-Compiler on the webserver - see [http://lesscss.org/#usage](http://lesscss.org/#usage "lesscss.org") for details.

Make `lessc` executable by the webserver and (if it is not in `%PATH%`) change the name and path in `app.yml`.

## Configuration ##
Add this configuration in
*apps/front/config/app.yml*

    all:
	  lessCss:
	    # executable can be the less-compiler executable or empty for client-side rendering (not supported yet)
	    executable: lessc
    	# less-javascript file (see less.org)
    	lessjs: less-1.4.2.min.js
 

## Usage ##
Simply put your less file(s) in *view.yml* (eg. *apps/front/config/view.yml*) like

    default:
	  http_metas:
	    content-type: text/html
	  stylesheets:
	    - layout
	    - mystyle.less
	    - main
	    - markdown
	  javascripts:
	    - front
	  has_layout:     true
	  layout:         layout

In the example above, the file *mystyle.less* will be automatically compiled to *mystyle.css* and included instead of the *.less*-file.

