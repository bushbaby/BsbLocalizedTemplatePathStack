BsbLocalizedTemplatePathStack
=============================

BsbLocalizedTemplatePathStackResolver is a small ZF2 module that provides a template path stack resolver to load templates named to the active locale.

## Installation

### as zf2 project

BsbLocalizedTemplatePathStackResolver works with Composer. To install it into your project, just add the following line into your composer.json file:

    "require": {
        "bushbaby/bsb-localized-template-path-stack-resolver": "*"
    }
   
Then update your project by runnning composer.phar update. 

Finally enable the module by adding BsbLocalizedTemplatePathStackResolver in your application.config.php file. 

### as standalone

For development purposes you might want to install BsbLocalizedTemplatePathStackResolver standalone. Clone the project somewhere on your computer

    git clone git@github.com:bushbaby/BsbLocalizedTemplatePathStack.git BsbLocalizedTemplatePathStack
    cd BsbLocalizedTemplatePathStack
    curl -sS https://getcomposer.org/installer | php
    git checkout develop
    ./composer.phar install
    phpunit
    

## Configuration

To configure the module just copy the bsb_localized_template_path_stack.local.php.dist (you can find this file in the config folder of BsbLocalizedTemplatePathStack) into your config/autoload folder, and override what you want.

Note that the resolver is attached after the zend resolvers. This means that it will be only visited when those fail to resolve to a template.

The the resolver is automaticly configured with the paths supplied in the view_manager['template_path_stack'] option and it are these paths that are used to look for a localized version of a template.

### Options

**fallback_locale** string, defaults to null
   
The resolver will use this locale as fallback locale when the requested template has not been provided.

 		
**name_conversion_pattern** string, defaults to '#DIRNAME#/#FILENAME#/#LOCALE#.#EXTENSION#'

This pattern is used to look for a template. 

### Example

Given a directory structure and a template name of 'application/index' the default pattern will be able to succesfully resolve a template if the current locale is one of nl_NL, de_DE or en_UK.

	view
	  application
		index
	      index
	        nl_NL.phtml
	        de_DE.phtml
	        en_UK.phtml
			
Given a directory structure and a template name of 'application/index' the conversion pattern should be changed to '#DIRNAME#/#LOCALE#/#FILENAME#.#EXTENSION#'.
			
	view
	  application
		index
	      nl_NL
	        index.phtml
	      de_DE
	        index.phtml
	      en_UK
	        index.phtml

This will resolve to application/index/nl_NL.phtml when the current locale is nl_NL. When the current locale is en_US resolving will fail unless a fallback locale has been set (to one of the provided templates).

