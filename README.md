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



