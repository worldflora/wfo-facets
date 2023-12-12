# wfo-facets
Faceted organisation of WFO names/taxa

## Import data

### Data Sources & Facets
These are rarely added and so done through the DB or admin interface.

### Facet Values
These can be numerous (but shouldn't be) and so there is a script that reads 
a csv file plus passed the parameter of the facet name ID. First row is ignored. 
The first columns should be. Further columns are ignored.
- Title
- Description
- URL (optional)

### Facet Scores
There are many of these and so imported in batches as CSV files using a script.
The source ID is passed as a parameter to script.
First row of the CSV is ignored
The first Columns in the CSV are as follows, subsequent columns are ignored.
- Facet title or id 
- Facet value or id
- WFO ID
- Negated ("1" negates all other values ignored)

## Facet Ideas

### IUCN - Global Threat Level
- Least concern etc

### IUCN - Locally Threatened
- On a national list somewhere

### CITES 
- Do they have clear global categories?

### Country
- ISO 2 Letter designation for political boundaries

### Climatic Zone
- Arctic = Above 66.5 N or S
- Boreal = Approximately 50 to 66.5 N or S - variable with oceanic zones
- Temperate = 23.5 to 50 approximately N and S
- Subtropical = 23.5 to 35 N or S
- Tropical = between the tropics 23.5 N and S

### Habitat
- Forest
- Grassland
- Mountain
- Deserts
- Aquatic-freshwater
- Aquatic-marine
- Aquatic-coastal

### Pollination Vector
- Wind
- Insect
- Bats
- Birds
- Non-flying vertebrates
- Molluscs
- Crustaceans

### Leaf Form
- Simple
- Pinnate (all flavours)
- Palmate

### Signature Character
- Succulent
- Spiny
- Aromatic foliage
- Aromatic flowers
- Big tree
- Small herb

### Floral form
- Actinomorphic (multiple axes of symmetry)
- Zygomorphic (only one axis of symmetry)
- Asymmetric (No axis fo symmetry)
- Compound pseudanthium or umbel (Different flower shapes)
- Non-showy wind pollinated

### Habit

https://en.wikipedia.org/wiki/Raunki%C3%A6r_plant_life-form

https://en.wikipedia.org/wiki/Plant_life-form


### AusTraits as a source

https://traitecoevo.github.io/austraits.build/index.html
https://bie.ala.org.au/species/https:/id.biodiversity.org.au/taxon/apni/51286863

### Family keys as a source?

https://www.colby.edu/info.tech/BI211/PlantFamilyID.html

Also Delta Intkey of Angiosperms


## WFO Facet Package Format

All data import is done via a generic package format. Packages can be added manually or could be pulled in programmatically. We could perhaps use IPT as provider software.

The package consists of two files

### meta.json

This contains a description of where to place the data in the scores.csv file.

- provider_name: The name of the organization providing this data.
- provider_person: The contact person at the organization
- provider_email: The contact email for the person.
- action: INSERT | UPDATE. 
  - INSERT will add not only the scores.csv but also create any facets and values defined in this file. This may be restricted to moderator approved imports so we can make sure we have a consensus on facets and facet values.
  - UPDATE will add all the data in scores. 
- facet: Added or updated in INSERT mode. At least name is required in UPDATE mode.
  - name:  The name of the facet that the scores are for e.g. country_iso or habit. 
  - description:
- facet_values: Added or updated in INSERT mode
  - name
  - 
- sources: A list of the sources 
  - id: A UUID for the source
  - replace: boolean. If true all the existing scores for all names for this facet from this source will be removed before the ones in the source file are added. If false or absent then scores will be added/updated for the names listed in scores.csv.
  - citation: string to cite this source
  - URL: A link to retrieve this source. Recommended but not required.




### scores.csv

A comma separated text file (UTF-8) containing a header and four columns.

- wfo_id: The identifier for a name in the World Flora Online
- facet_value: The string name used for this facet value.
- negated: 0 or 1. A value of 1 is used this is asserting that this facet does NOT exist in this name. 
- source_id: The id of the source. This must already exist in the database or be defined in the meta.json