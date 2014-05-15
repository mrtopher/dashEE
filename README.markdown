# dashEE

## Control Panel Dashboard Framework for EE CMS

The idea behind dashEE is to create a completely customizable EE dashboard where users can choose the kind of information they want to see and developers can add custom functionality through the development of widgets. dashEE allows developers to create custom widgets that can be installed by themselves or that can be packaged with custom modules. Once uploaded to the server they are available for users to add to their dashboards with drag-n-drop simplicity in any order/configuration they wish. Check out the short video below to see it in action.

## Installation Instructions

### ExpressionEngine 2.x Requirements

* PHP 5.2+
* ExpressionEngine 2.2.2, or later.

### Installation

1. Extract the ZIP file.
2. Copy __third_party/dashee__ to your __/system/expressionengine/third_party/__ directory.
3. Copy __themes/third_party/dashee__ to your __/themes/third_party/__ directory.
4. Open the Module Manager (Add-ons > Modules), and install the module.
5. Give all member groups permission to access the module.

### Resolving Permission Issues

Because dashEE is a module you must ensure that all additional user groups you create in the system have permission to access it. Otherwise when your users log into the CP they will be met with a “Permission Denied” message.

User groups must be given permission to access the “Add-Ons” and “Add-Ons: Modules” sections, as well as the module itself, in order for users other than super-admins to access the module.

## Creating Widgets

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

Each widget you develop must be packaged with a module, whether they are really part of that module or not. If you are developing a stand alone widget you can simply place the widget file in the “widgets” directory of the dashEE module (*/system/expressionengine/third_party/dashee/widgets*). You could also build a blank module with a “widgets” directory and that will accomplish the same thing.

You can save yourself some time and get a jump start on your widget development by downloading the [widget boilerplate](http://www.christophermonnat.com/wp-content/uploads/2011/09/wgt.biolerplate.php_.zip). This widget template will get you up and running quickly.

### Simple Widgets

Simple widgets provide very basic reporting/informational functionality to users without updatable settings or interactivity. Examples of simple widgets from the list of widgets that are provided with the module are EE Create Links, EE Modify Links and EE View Links.

To create your own simple widget:

1. Create a new widget file called wgt.test_widget.php. *Note: we are using a similar naming convention to that used by EE to create add-ons.*

2. Create a new class called __Wgt_test_widget__. Each widget file must have at least 2 things: a title variable which is used to display the widget title and a function called index.

        <?php
        class Wgt_test_widget
        {
            public $title;

            public function index()
            {

            }
        }

3. In the index function, set the value of the title variable to whatever you want to display as the title of your widget and return whatever content you want displayed in the content area (formatted as HTML).

        <?php
        class Wgt_test_widget
        {
            public $title;

            public function index()
            {
                $this->title = 'Test Widget';
                return '<p>This is a test widget.</p>';
            }
        }

4. Now that we have our widget we need to add the widgets name and description to the language file for the ‘installer’. This is what will display when users click __Widgets__ from the dashboard and see all available widgets for them to add. Open the language file for the dashEE module (*/system/expressionengine/third_party/dashee/widgets/language/english/lang.dashee.php*) and add the following 2 new lines to the bottom of that file:

        'wgt_test_widget_name' => 'Test Widget',
        'wgt_test_widget_description' => 'This is the test widget I just developed.',

5. Save your changes and upload your widget file to the ‘widgets’ directory of the dashEE module (*/system/expressionengine/third_party/dashee/widgets*) and update the language file as well.

6. Log in to EE and click __Widgets__ on the top right of the dashEE dashboard. You should now see the widget we just added in the list. Click __Add__ next to it and you should see it added to your dashboard.

### Advanced Widgets

The above was an example of creating a simple widget that just displays text. But what about creating widgets that can actually do things? Creating widgets like the included RSS feed reader and blank widget requires adding settings to your widget. For this example we’re going to dissect the RSS Feed Reader widget. Open the RSS feed reader widget located at */system/expressionengine/third_party/dashee/widgets/wgt.feed_reader.php* to follow along.

The first thing to notice is that we have added a new class variable called $settings and loaded it with an array in the constructor. This variable is what dashEE looks for when determining if your widget is dynamic or static. When adding settings to your widget you must always provide defaults in the constructor and those defaults are saved to the users dashEE settings when the widget is first added to their dashboard.

    class Wgt_feed_reader
    {
        public $title;
        public $settings;

        public function __construct()
        {
            $this->settings = array(
                      'url' => 'http://expressionengine.com/feeds/rss/eeblog/',
                      'num' => 5
                      );
        }

The index function of this widget contains some logic instead of just returning static content like in the simple widget above. You will notice that you can still gain access to the EE object by using the get_instance() function just like in modules and other EE add-ons. This index method uses the values stored in settings to go and get the contents of an RSS feed and return it as an unordered list. One thing to note here is that $settings is passed as an argument to index. It’s important not to overlook this because if it's not included then the module will be unable to pass the users saved settings to your widget upon load.

    public function index($settings = NULL)
    {
        $EE = get_instance();
        $EE->load->helper('text');

        $rss = simplexml_load_file($settings->url);

        $display = '';
        $i = 0;
        foreach($rss->channel->item as $key => $item)
        {
            if($i >= $settings->num) { break; }

            $link  = trim($item->link);
            $title = trim($item->title);

            $display .= '<li>'.anchor($link, $title, 'target="_blank"').'</li>';
            ++$i;
        }

        $this->title = ellipsize($rss->channel->title, 19, 1);

        return '
            <ul>'.$display.'</ul>
            ';
    }

Finally there is a new method in this widget called settings_form(). This method is called when a user clicks the settings icon for the widget. At this time you simply need to return an HTML form with fields named the same as the keys in the default settings array you provided in the constructor. dashEE takes care of displaying the form and saving the users settings in the database.

    public function settings_form($settings)
    {
        return form_open('', array('class' => 'dashForm')).'

            <p><label for="url">Feed URL:</label>
            <input type="text" name="url" value="'.$settings->url.'" /></p>

            <p><label for="num">Number of Posts:</label>
            <input type="text" name="num" value="'.$settings->num.'" /></p>

            <p><input type="submit" value="Save" /></p>

            '.form_close();
    }

### Interactive Widgets

Simple widgets are perfect for static content and advanced widgets are great for those that require very basic input/output but what if you want to do more? With dashEE 2.0 you now have the tools to develop truly interactive widgets that respond to custom user input/submissions and events.

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
