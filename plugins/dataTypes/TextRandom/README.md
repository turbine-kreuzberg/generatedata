## TextFixed Data Type

This Data Type generates a random number of random words, taken from lorem ipsum. You can specify the min and max
number of words. 


### Example API Usage

```javascript
{
    "numRows": 20,
    "rows": [
        {
            "type": "TextRandom",
            "title": "text",
            "settings": {
                "startsWithLipsum": false,
                "minWords": 2,
                "maxWords": 10
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
