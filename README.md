# Excel Export for Opencart

Excel Export allow you to easily export all your orders into an Excel files. You can choose to export as invoice or list.


## Requirements

* Opencart 1.5.3.x to 1.5.5.x
* vqmod opencart module
* PHP5

## Installation

_[Manual]_

* Download this: [https://github.com/SoftMonkeyJapan/opencart-excel-export/archive/1.5.3.x-to-1.5.4.x.zip](https://github.com/SoftMonkeyJapan/opencart-excel-export/archive/1.5.3.x-to-1.5.4.x.zip)
* Unzip that download.
* Go into the unzip folder
* Merge the `system` folder into your `system` opencart directory
* Merge the `admin` folder with your `admin` opencart directory
* Merge the `vqmod` folder with your `vqmod` opencart directory


## Usage

### Initialization

By default, you won't be able to use the component due to permissions restrictions. In order to change this behavior, go under `System->Users->Users Groups` and then edit the group you want to allow the access. 

In `Access Permission` and `Modify Permission`, check the item `report/export_xls` and then save. You can now use the component.


### How to use

For more details on how to use it, go to http://kartono-loic.com/app/webroot/excel-export/documentation/


## Thanks

It's important to thanks poeple that helped me improve this extension : 
- Laurent Chapin
- 阿維
- Ilsur Bilalov

## License

You are allowed to use it for whatever you want and whenever you want.
