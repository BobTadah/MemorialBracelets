# Memorial Bracelets XSLT Export System

In this directory you will find several files, including:

* combine.php
* order.xml
* \*.xslt

## \*.xslt

These are the XML Stylesheet Transform files.  These are what Xtento OrderExport uses to convert the order into a 
SpreadsheetML language.

Knowledge of XSL and XPath will be necessary to modify these files.

In general, these files follow the same system throughout.  We use foreach loops with xpaths that target distinct 
upper-level headings.

For example:

```xml
<xsl:for-each select="
/objects/object/items/item[
    not(
        custom_options/custom_option[bracelet_piece='engraving_style']/value = preceding::item[
            not(product_type='configurable')
            and product_attributes/product_type='Paracord'
        ]/custom_options/custom_option[bracelet_piece='engraving_style']/value)
        
    and not(product_type='configurable')
    and product_attributes/product_type='Paracord'
]">
    <xsl:sort select="custom_options/custom_option[bracelet_piece='engraving_style']/value"/>
    <!-- ... -->
</xsl:for-each>
``` 

The bottom two parts (configurable and product_attributes/product_type) are for determining if it's a valid product, and
will be repeated in every xpath selector.  The real important part is the upper part + the sort.

The upper part ensures that this item's engraving style is not the same as the previous item's engraving style.  We then
ensure the items applied to this selector are sorted by engraving style, and this means that we essentially get one item
per every engraving style.  We'll then store the engraving style in a variable:

```xml
<xsl:sort select="custom_options/custom_option[bracelet_piece='engraving_style']/value"/>
```

and use that variable as part of the xpath for future selectors, to ensure an item is only selected once:

    and custom_options/custom_option[bracelet_piece='engraving_style']/value = $engravingStyle
    
## order.xml

This is what Xtento uses to translate an Order using the XSLT.  On Memorial Bracelets production, you can fetch a copy
of this file by running the "Developer Debug" export.

## combine.php

This file takes all the XSLT files and combines them into the single XML expected for the Xtento Export profile.
