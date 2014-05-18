# dashEE

## Control Panel Dashboard Framework for EE CMS

The idea behind dashEE is to create a completely customizable EE dashboard where users can choose the kind of information they want to see and developers can add custom functionality through the development of widgets. dashEE allows developers to create custom widgets that can be installed by themselves or that can be packaged with other modules. Once uploaded to the server they are available for users to add to their dashboards with drag-n-drop simplicity in any order/configuration they wish.

## Installation Instructions

### ExpressionEngine 2.x Requirements

* PHP 5.2+
* ExpressionEngine 2.8, or later.

### Installation

1. Extract the ZIP file.
2. Copy __third_party/dashee__ to your __/system/expressionengine/third_party/__ directory.
3. Copy __themes/third_party/dashee__ to your __/themes/third_party/__ directory.
4. Open the Module Manager (Add-ons > Modules), and install the module.
5. Give all member groups permission to access the module.

### Resolving Permission Issues

Because dashEE is a module you must ensure that all additional user groups you create in the system have permission to access it. Otherwise when your users log into the CP they will be met with a “Permission Denied” message.

User groups must be given permission to access the “Add-Ons” and “Add-Ons: Modules” sections, as well as the module itself, in order for users other than super-admins to access the module.

## Widget Overview

dashEE comes with a number of default widgets including:

* __RSS Feed Reader__ – configurable to read any valid RSS feed
* __Task List__ - simple interactive widget for managing task lists
* __Blank Widget__ – empty widget whose title and content can be configured using widgets settings
* __EE Create Links__ – create links as seen on default EE CP home
* __EE Modify Links__ – modify links as seen on default EE CP home
* __New Members__ – shows 10 most recent EE website members
* __Recent Entries__ – shows 10 most recent EE entries
* __EE View Links__ – view links as seen on default EE CP Home

The real power behind the module is when you start developing your own widgets either as stand alone tools or as part of your custom modules.

Each widget you develop must be packaged with a module, whether they are really part of that module or not. If you are developing a stand alone widget you can simply place the widget file/folder in the “widgets” directory of the dashEE module (*/system/expressionengine/third_party/dashee/widgets*). You could also build a blank module with a “widgets” directory and that will accomplish the same thing.

## Creating New Widgets

You can save yourself some time and get a jump start on your widget development by downloading the [widget boilerplate](http://www.christophermonnat.com/wp-content/uploads/2011/09/wgt.biolerplate.php_.zip). This widget template will get you up and running quickly.


### New in dashEE 2.0

With the release of dashEE 2.0 come some exciting new developer features:

* __Widget Folders:__ widgets can now be packaged in their own folder within the widgets directory of a module. This is especially helpful for the development of interactive widgets.
* __Widget Models & Views:__ widgets can now support their own model and view files giving you the same power and flexibility that you have when building other EE add-ons like modules.
* __Install/Uninstall Methods:__ you can now include install and uninstall methods in your widget to facilitate actions that need to happen when a widget is first used and when it is removed.
* __Add/Remove Methods:__ similar to install/uninstall methods, add/remove methods fire every time a user adds or removed a widget from a dashboard. Perfect for cleaning data out of a DB table, etc.
* __Widget Specific JS:__ you can now include custom widget specific JavaScript with your widget and leverage the new dashEE jQuery plugin to facilitate GET/POST requests with custom widget methods in your widget file.

### Your First Widget

A widget, in it's most basic form, is just a packaged snippet of HTML that can be placed on and moved around dashboards. Widgets can be just simple static HTML that is displayed for users to reference all the way up to self contained little apps that process and respond to user input.

For your first widget you're going to develop a simple static widget to get the feeling for how widgets are developed and added to dashEE. Before you get started you may want to look at some examples of simple widgets that are provided with the module like EE Create Links, EE Modify Links and EE View Links.

To create your own simple widget:

1. Create a new widget file called wgt.test_widget.php. *Note: we are using a similar naming convention to that used by EE to create other add-ons.*

2. Create a new class called __Wgt_test_widget__. Each widget file must have at least 2 things: a title variable which is used to display the widget title and a function called index.

        class Wgt_test_widget
        {
            public $title;

            public function index()
            {

            }
        }

3. In the index function, set the value of the title variable to whatever you want displayed in the widget title bar and return whatever HTML content you want displayed in the content area.

        class Wgt_test_widget
        {
            public $title;

            public function index()
            {
                $this->title = 'Test Widget';
                return '<p>This is a test widget.</p>';
            }
        }

4. If you were to go to the dashboard now and click __Widgets__ you would see your widget is listed but the name and description are not customized. You have two options for accomplishing this: add a $widget_title and $widget_description variable to your widget file or add them to the module language file the widget is packaged with. Since this is a simple widget we're going to opt for the variable approach:

        class Wgt_test_widget
        {
            public $widget_title         = 'Test Widget';
            public $widget_description   = 'This is an example of a simple widget.';

            public $title;

            public function index()
            {
                $this->title = 'Test Widget';
                return '<p>This is a test widget.</p>';
            }
        }

5. Save your changes and upload your widget file to the *widgets* directory of the dashEE module (*/system/expressionengine/third_party/dashee/widgets*) and update the language file as well (if you touched it).

6. Log in to EE and click __Widgets__ on the top right of the dashboard. You should now see the widget we just added in the list. Click __Add__ next to it and you should see it added to your dashboard.

### Widget Settings

In the example above you created a simple widget with static HTML content that users can add to their dashboards for reference. Now we will explore how you can add settings to your widget that users can then customize. Continuing with the above example, lets modify the test widget we've been building to make the widget title and body something that the user can customize and set to whatever they like after it's been added to their dashboard.

1. Open the __wgt.test_widget.php__ file and add a new variable called $settings. The $settings variable is a attribute that dashEE looks for and automatically adds functionality to your widget on the front end.

2. Create a constructor method for the Wgt_test_widget class and set the $settings variable with an array of default settings for the widget. If you are going to provide users with settings then you are required to provide default values that dashEE can use when the widget is first added to a users dashboard.

        class Wgt_test_widget
        {
            public $widget_title         = 'Test Widget';
            public $widget_description   = 'This is an example of a simple widget.';

            public $title;
            public $settings;

            public function __construct()
            {
                $this->settings = array(
                          'title' => 'Test Widget',
                          'body'  => '<p>Body goes here.</p>'
                          );
            }

            public function index()
            {
                $this->title = 'Test Widget';
                return '<p>This is a test widget.</p>';
            }
        }

3. Now that we have settings in the widget we can use them in our index method.

        public function index($settings = NULL)
        {
            $this->title = $settings->title;

            return $settings->body;
        }
First thing to notice is the $settings argument. If you want to use your widgets settings in any method in your widget file you need to provide a $settings argument so dashEE can pass them in. Settings are stored in the database as a JSON string and are decoded as an object before being passed to the widget file so you should be able to use PHPs arrow syntax with the same index names you used when defining your defaults.

4. Last thing to do is provide a settings form so users can edit the widget settings themselves. Add a new method called settings_form() with one argument for $settings.

        public function settings_form($settings)
        {
            return form_open('', array('class' => 'dashForm')).'

                <p><label for="title">Widget Title:</label>
                <input type="text" name="title" value="'.$settings->title.'" /></p>

                <p><label for="body">Widget Body:</label>
                <textarea name="body">'.$settings->body.'</textarea></p>

                <p><input type="submit" value="Save" /></p>

                '.form_close();
        }
dashEE takes care of the submission and processing of widget settings, all you need to do is return an HTML form with inputs matching up to the default settings you defined in the beginning and the module will take care of the rest.

5. Save and upload your changes. If you already have the widget on your dashboard you'll need to remove it and re-add it so the settings can take effect. Once added you'll notice there is a new gear icon in the widget header when you mouseover. Click that icon and you should see your settings form displayed. Make whatever changes you wish and click __Save__. You should now see your customized settings reflected in the widget.

### Interactive Widgets

We have built a simple static widget and added some settings to make it a little more interactive. But what if we wanted to build a truly interactive widget with forms that respond to GET/POST requests and that has a little JS sprinkled in as well? With the release of dashEE 2.0 this is now possible with some new widget methods:

__widget_install() & widget_uninstall()__

Widgets now have the same capabilities as modules with their own install and uninstall methods. This is the perfect place to add/remove DB tables to go along with your widget. These methods are run when the widget is added to the very first dashboard in your install and when the very last instance of a widget is removed.

__widget_add() & widget_remove()__

The install and uninstall methods are run only when a widget is added to a dashboard the *first time* and removed the *last time* in an install. The add and remove methods are run *every time* a user adds or removes the widget from their dashboard. So this is the place to populate the DB with member defaults or do whatever prep needs to be done when a widget is first added to a dashboard or tear down when it is removed.

__dashEE jQuery Plugin__

You now have the ability to process/respond to custom GET/POST requests with the dashEE jQuery plugin. All requests from your widget are handled via AJAX through the dashEE module which routes requests back to your widget file for processing. Simply add a JS file to your widget and use the provided __$(this).dasheeGetProxy({})__ and __$(this).dasheePostProxy({})__ methods and you'll be good to go.

The task list widget (provided as one of the defaults) is a great example of an interactive widget. Review the files located at */system/expressionengine/third_party/dashee/widgets/tasklist* for details.

***

### Widgets and Permissions

Not all widgets are created equal. It’s possible that even though a module is installed you may still not want a user to be able to add a given widget to their dashboard unless they have certain permissions. This can be accomplished by adding an additional method to your widget files called permissions(). The example below is from the EE Create Links widget (wgt.create_links.php).

    public function permissions()
    {
        if(!$this->_EE->cp->allowed_group('can_access_publish') && (!$this->_EE->cp->allowed_group('can_access_edit') && !$this->_EE->cp->allowed_group('can_admin_templates')) && (!$this->_EE->cp->allowed_group('can_admin_channels')  && ! $this->_EE->cp->allowed_group('can_admin_sites')))
        {
            return FALSE;
        }

        return TRUE;
    }

If your widget has a permissions() method that method will be run before allowing users to add the given widget to their dashboard. You can add any logic you want in the permissions method as long as you return TRUE for the user can access the widget or FALSE if they cannot. You can see examples of implementing permissions in the create, modify and view link widgets included with the module.

## Links

* [Documentation](http://chrismonnat.com/code/dashee)
* [Widget Directory](http://mrtopher.wufoo.com/reports/z5p8w8/)
