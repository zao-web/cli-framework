# CLI Framework

A WP-CLI base framework for building WP-CLI workflows.

When using in your plugin, you will need to take several steps:

* Copy the `cli` directory to your plugin. To include the CLI commands from your plugin, review the [example-cli-plugin.php](https://github.com/zao-web/cli-framework/blob/master/example-cli-plugin.php#L147-L164) file.
* Determine how you load your classes, and replace the example plugin namespaces.
* Replace the sample commands in [`cli/commands.php`](https://github.com/zao-web/cli-framework/blob/master/cli/commands.php) with your own.
* Replace the sample methods in [`cli/actions.php`](https://github.com/zao-web/cli-framework/blob/master/cli/actions.php) with your own.
