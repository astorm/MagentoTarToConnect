MagentoTarToConnect
===================

A small shell script to automatically package tar archives into Magento's Connect 2.0 format. 

### Description

Under the hood Magento Connect 2.0 packages are actually tar'ed and gziped files with a specially formatted package manifest.  Well, they're almost `tar` and `gzip` files.  Magento implemented their own archiving and unarchiving code in PHP, and this code occasionally has problems with tar archives created via standard OS tools. 

This shell script will take a standard tar archive, untar it, build the Connect `package.xml` manifest, and then re-tar and gzip the files **using Magento's code** (included in the `vendor` library here, but you can substitute your own).  This decreases the likelyhood yoru package will be incompatible with Magento Connect. 

*We're currently migrating all open source Pulse Storm projects into individual repositories.  We'll have an example of how this works up ASAP*