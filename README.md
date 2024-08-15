# wfo-facets
A faceted classification service for WFO names/taxa

"A faceted classification is a classification scheme used in organizing knowledge into a systematic order. A faceted classification uses semantic categories, either general or subject-specific, that are combined to create the full classification entry. Many library classification systems use a combination of a fixed, enumerative taxonomy of concepts with subordinate facets that further refine the topic."

https://en.wikipedia.org/wiki/Faceted_classification

## Design

We need to make subsets of taxa which have particular attributes. e.g. List all the trees. List all the plants in Belgium. List all the trees in Belgium. etc. To do this we need a vocabulary of attributes and then expert curated lists of what taxa possess attributes (i.e. tree experts to maintain the tree list and Belgium experts to maintain the list of what occurs in Belgium).

### Facets, Facet Values and Data Sources

The attributes that taxa  possess and are used for subsetting are organized as Facets and Facet Values. These are synonymous to character and character states often used in identification keys but the faceting terminology is adopted to show the primary purpose is information retrieval not identification. 'Habit' is an example of a facet and 'tree' of facet value.

The expert group who maintain the association between a name and a facet value are called the Data Source. A Data Source is a single list of names that possess a facet value. An organisation might be responsible for multiple Data Sources. The DA for the facet value "IUCN Category:Critically Endangered" would be the IUCN's list of critically endangered plant species. The DA for "IUCN Category:Least Concern" would be the IUCN's list of plants of least concern. In this example one organisation, IUCN, would be responsible for multiple data sources.

A facet value may have multiple DAs. The World Checklist of Vascular plants supplies distribution data that can be rendered as ISO countries DA

Each facet value might have multiple Data Source. 

### Names, Taxa and Inheritance of Facet Values

Facets are linked to __Names__ not Taxa. The facet service holds no knowledge of the placement of names within a taxonomy. This is for two reasons:

1. Most data available for inclusion will be tagged with Names (typically in the form of name strings) not to specific taxa. 
2. The WFO taxonomy will change as our knowledge improves and the scoring of taxa needs to change too.

During the process of indexing the facet service will use the WFO Plant List index to calculate the facet values for a particular __Taxon__. The Plant List will provide the taxonomic placement of names and the facet service will follow these rules:

1. Starting at the root of the classification and working down the hierarchy to the target taxon each facet value of each __name__ encountered will be added to the list of facet values for the taxon. This means that all the species within a family of trees don't individually have to be scored as trees.
2. Facet values of the immediate synonyms of the taxon will be added to the taxon.

This algorithm expands on our knowledge of the hierarchy without getting overly complex and leading to unexpected consequences as might occur if we included all the synonyms of all the parent taxa or subtaxa. By and large species (or their synonyms) will be scored to facet values. Occassionally genera or families will be scored for facet values that are uniform for all species.

### Import formats

Lists are maintained directly through the user interface or by providing CSV files. These CSV files are either uploaded or pulled from a URL. The first column of the CSV file should contain valid ten digit WFO IDs. Any other values are ignored. Any other columns in the CSV file are ignored. The [name matching tool](https://list.worldfloraonline.org/matching.php) produces files in the right format to be uploaded to the facet service.

### Implementation

The facet server is implemented as a MySQL 8.* database with a PHP based web interface. This allows users and administrators to login and manage facets, facet values and sublists of names that have those facet values. The facet server calls an instance of the WFO Plant List application API to facilitate this. No nomenclatural or taxonomic data are stored in the facet server, only WFO IDs. There is, however, a caching of nomenclature in the server and in the clients browser for performance reasons. These caches are perioidally purged.

At index time a script on the facet server calls a SOLR index directly and __updates__ the SOLR document for a taxon. This can either be done for individual taxa or as a batch process. This is a process of _decorating_ an existing index that was created from a WFO Plant List data release. The index already contains the nomenclatural and taxonomic information for the complete list of vascular plants and bryophytes. The facet indexing process adds fields containing data from the facet server to these existing SOLR documents.

For performance reasons the data that is added to each taxon document for faceted searching is just the facet IDs and IDs representing the provenance. A separate process makes SOLR documents containing labels for facet, facet values and data sources. This acts as a data dictionary so that we can render the facet information in a human readable way. Additionally label data is added to each taxon document as text so that those documents will be found in free text searches using words in facet labels.



## Facet Ideas

Below is just a brain dump of some ideas for facets

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