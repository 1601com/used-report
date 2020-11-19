<a href="https://www.1601.com" ><img align="right" src="https://user-images.githubusercontent.com/40162179/98120299-14f9f380-1eae-11eb-912e-96d7ecaeebcb.png" width="100" /></a>
<br>
## Used-Report-Bundle
The purpose of this extension is to provide an overview for the usage of images in your Contao-System.   
This includes:
- A direct link to the backend-element in which the image is used
- A frontend view for the used image if it exists and is published
- A notice if the image was used via insert-Tag
- A notice if the image was used in a stylesheet

### Get the overview
To check for the use of your images simply navigate to the filemanager and click <kbd>UseReport</kbd>   

### Settings
The extension uses bash-commands to search for images that are not used in the database.   
Therefore it may need a lot of server performance and it is advised to cap the number of simultaneous connections.   
This value can be set in the "Used-Report" entry point in your backend.   

###### Please Note
The extension is still not fully developed yet and may not get all the used images.   
It will mostly find images used in core elements.  

For example:
- The extension can find images used in content, article, news, event, .. - elements   
- The extension can not find images used in extensions like metamodels. 

**Only delete images if you are confident that they are not in use!**
