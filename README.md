# Tabular field

The Tabular field allows you to create a generic table field within an entry. You can set a comma separated list of columns name. Tabular use [jsGrid jquery plugin](https://github.com/tabalinas/jsgrid) to edit table.

This extension is part of a project I made here in my company and it is very specific for that purpose. I'll leave as is also if there are some features not really useful for a generic use. It works for my needs but surely there are some bugs.

## Installation

This extension was only tested on Symphony 2.4 (because it is the version I use and I haven't time right now to try different version)

1. Upload the `/field_tabular` folder to your Symphony `/extensions` folder.
2. Enable it by selecting the "Tabular field", choose Enable from the with-selected menu, then click Apply.
3. You can now add the "Tabular" field to your Sections.

## Options

### Columns name

Comma separated list of columns name. These names will be used if `Header` flag is set.

### Header

Choose to show or not Table Header

## Datasources

Tabular data will be output as a simple table-like XML structure.

```xml
  <field mode='normal'>
    <table>
      <head>
        <cell>Dati</cell>
        <cell>Valore</cell>
      </head>
      <row>
        <cell>Dimensioni</cell>
        <cell>490 x 690 x 1490 mm</cell>
      </row>
	  <row>
	    ...
	  </row>
    </table>
  <field>
```
