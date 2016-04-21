## Float Data Type

This Data Type generates random float numbers according to whatever format you want. Note: for the 
placeholder strings, documentation for this Data Type in the generatedata UI. That provides the list of available
placeholders.

### Example API Usage

This example generates two types of random float numbers, one with 2 decimal places and the point (.) as decimal separator, e.g. 123.45 and the other with 4 decimal places and the point (,) as decimal separator, e.g. 987,6543. 
POST the following JSON content to `http://[your site]/[generate data folder]/api/v1/data`:

```javascript
{
    "numRows": 10,
    "rows": [
        {
            "type": "Float",
            "title": "Random Float",
            "settings": {
                "min": "10",
                "max": "500",
                "decimalPoint": ".",
                "decimalPlaces": "2"
            }
        },
        {
            "type": "Float",
            "title": "Random Float",
            "settings": {
                "min": "-50",
                "max": "50",
                "decimalPoint": ",",
                "decimalPlaces": "4"
            }
        }
    ],
    "export": {
        "type": "JSON",
        "settings": {
            "stripWhitespace": false,
            "dataStructureFormat": "simple"
        }
    }
}
```

### API help

For more information about the API, check out:
[http://benkeen.github.io/generatedata/api.html](http://benkeen.github.io/generatedata/api.html)
