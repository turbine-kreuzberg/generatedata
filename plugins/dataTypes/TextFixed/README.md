## TextFixed Data Type

This Data Type generates a fixed number of random words.  


### Example API Usage

```javascript
{
    "numRows": 20,
    "rows": [
        {
            "type": "TextFixed",
            "title": "text",
            "settings": {
                "numWords": 5 
            }
        }
    ],
    "export": {
        "type": "JSON",
        "settings": {
            "stripWhitespace": false,
            "dataStructureFormat": "complex"
        }
    }
}
```
 
### API help

For more information about the API, check out:
[http://benkeen.github.io/generatedata/api.html](http://benkeen.github.io/generatedata/api.html)
