# Fieldbook for Gravity Forms

A [Gravity Forms](http://www.gravityforms.com/) add-on for integration with [Fieldbook](http://fieldbook.com/)

## How to Use

This documentation assumes you already know how to install and use a WordPress plugin and Gravity Forms. Also assumed: you know how to use Fieldbook.

The plugin requires that you have a Fieldbook API key. To generate a key, click the "API" link in the upper-right corner of a Fieldbook sheet, then click "Manage API Access" and copy the Base API URL. Then, click "Generate a new API key." Copy the API key and password that is generated.

Once you have your API key and secret, log in to your WordPress admin interface. Under the "Forms" menu, go to "Settings," then paste in the Base API URL, API key and password, and then click "Update Settings."

For any form you build, you will see a "Fieldbook" option listed under each form's settings. Select whether you want the Fieldbook feed to only create new records, or update existing records. Then, choose your fieldbook sheet, and map the fields from your Fieldbook sheet to your form fields.

If you chose the Update option, you will also need to specify a "key" field. The record that is updated is based on the key field you specify. For example, if you have a "Name" column in your Fieldbook sheet, where each name is unique, Fieldbook for Gravity Forms will search for a record with a matching name and update that record upon form submission. If a matching record is not found, a new record will be created.

You may need to create more than one Fieldbook feed if form submissions use fields that can be found in more than one sheet. For example, a donation form may need to create new records in your Donations sheet, but update existing records in your Donors sheet. Be careful in these types of situations and understand that the order that you create your feeds matters. To use the donation form example, make sure to create the "donors" feed first, then the "donations" feed after that. The reason: each donation must include a link to the donor. Fieldbook for Gravity Forms executes its feeds in the order they are created. If a donor doesn't exist when the donation record is created, then the donation record will not be linked to the proper donor.

## Pull Requests Welcome

This add-on makes use of the [Phieldbook](https://github.com/Joeventures/phieldbook) PHP client for Fieldbook, which I also wrote. If there are updates you would like to see to Phieldbook, please feel free to submit your Pull Requests to the Phieldbook repository.

For anything else related to Fieldbook for Gravity Forms, including the functionality or this terrible documentation, please feel free to submit your Pull Requests to this repository. Your submissions are welcome.

## API References

* [Gravity Forms Add-On Framework](https://www.gravityhelp.com/documentation/category/add-on-framework/)
* [Fieldbook](https://github.com/fieldbook/api-docs)

## Credits

Thanks to the people at Fieldbook, @jasoncrawford in particular; and to @carlhancock at Gravity Forms for your feedback and advice along the way.