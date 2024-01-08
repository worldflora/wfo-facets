# wfo-facets
A faceted classification service for WFO names/taxa

"A faceted classification is a classification scheme used in organizing knowledge into a systematic order. A faceted classification uses semantic categories, either general or subject-specific, that are combined to create the full classification entry. Many library classification systems use a combination of a fixed, enumerative taxonomy of concepts with subordinate facets that further refine the topic."

https://en.wikipedia.org/wiki/Faceted_classification

## Design

We need to make sets of taxa which have particular attributes or characteristics. e.g. List all the trees. List all the plants in Belgium. List all the trees in Belgium. etc. To do this we need a common, community curated vocabulary of attribute names. We use Wikidata for this. In fact we go to the extreme. The application only defines  the relationship between WFO IDs and Wikidata Q numbers. It caches some display strings locally but this is only for performance purposes. All entities are described elsewhere. This means it will be easy to internationalize/localize human facing interfaces. It will also facilitate possible ingestion of data into Wikidata at some point in the future should that be required.

## Facets and Facet Values

The categories that taxa are placed in are organized into Facets and Facet Values. These are synonymous to Character and Character States often used in identification keys but the facetting terminology is adopted to show the primary purpose is information retrieval not identification.

An example of a Facet is Country ([Q25275](https://www.wikidata.org/wiki/Q889)) and a Value of that facet is Afghanistan ([Q889](https://www.wikidata.org/wiki/Q889)).


## Names, Taxa and Inheritance of Facet Values

Facets are linked to __Names__ not Taxa. The facet service holds no knowledge of the placement of names within a taxonomy. This is for two reasons:

1. Most data available for inclusion will be tagged with Names (typically in the form of name strings) not to specific taxa. 
2. The WFO taxonomy will change as our knowledge improves and the scoring of taxa needs to change too.

During the process of indexing the facet service will use the WFO Plant List index to calculate the facet values for a particular __Taxon__. The Plant List will provide the taxonomic placement of names and the facet service will follow these rules:

1. Starting at the root of the classification and working down the hierarchy to the target taxon each facet value of each __name__ encountered will be added to the list of facet values for the taxon. This means that all the species within a family of trees don't individually have to be scored as trees.
2. If the facet scoring has the _negated_ flag set it will be removed from the list. Thus an entire family could be scored as having four petals but that character negated for the one six petaled genus and its species.
3. Facet values of the immediate synonyms of the taxon will be added to the taxon unless they are negated. A negated value in a synonym does not negate the value of the accepted name.

This algorithm expands on our knowledge of the hierarchy without getting overly complex and leading to unexpected consequences as might occur if we included all the synonyms of all the parent taxa or subtaxa.


## Import formats

Import formats are very simple.

Facets and their facet values are imported as a CSV file with the first column being the facet Q number and the second column being the Q number for the facet value. Further columns are ignored but useful for debugging.

Facet value scores are imported as a CSV file with the first column being a WFO ID, the second column being a facet value Q number and the third column being the negated flag (0/1) . Subsequent columns are ignored. The source for the assertions is also represented as a Q number. It is placed at the end of the file name following an underscore. e.g. country_distributions_Q25275.csv is the sourced from [Plants of the World Online](https://www.wikidata.org/wiki/Q47542613).

The first line of both files is assumed to be a header line and ignored.

It is not necessary for facet values to have already been imported in order for names to be scored to them. Scorings could use entirely novel values that are later attributed to facets so they become available for export. The curation of facet values into facets is a role of the facet service.


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
- Forest Q4421
- Grassland Q1006733
- montane Q112229112
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


## Wikidata Linking

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