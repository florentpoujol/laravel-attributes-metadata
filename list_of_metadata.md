




- string / char
	- cd: varchar (255)
	- rules: max: 255
	- cast: none
	- nova: Text

- uuid special kind of string

- text
	- cd: text
	- rules: max: 16548
	- cast: none
	- nova: textare

- enum (+ values)
	- cd: enum
	- rules: in
	- cast: none
	- nova: select

- set (+ values)
	- cd: set
	- rules: ?
	- cast: none
	- nova: multipleselect / checkbox group

- boolean (+ default)
	- cd: boolean
	- rules: boolean
	- cast: boolean
	- nova: boolean

- json
	- cd: json
	- rules: none
	- cast: array
	- 

- date / datetime / timestamp
	- cd: date/time/stamp
	- rules: date
	- cast: datetime
	- nova: date/datetime

- int
	- cd: int
	- rules: numeric
	- cast: int
	- nova: Number

- float
	- cd: float
	- rules: numeric
	- cast: float
	- nova: Number

- belongsto
	- cd: unsigned integer index
	- rules: exists {othertable}.{their key}
	- cast: none
	- Nova: BelongsTo
	- values are the constructor argument

- HasMany
	- cd: none
	- rules: none
	- cast: none
	- Nova: HasMany
	- values are the constructor argument



caractéristiques qui peuvent changer suivant le model où sont utilisé
- default value
- possible values (enum/set)
- length (string/int/float)
- 


other important metadata to be able to be set
- guarded
- fillable
- hidden
- nullable
- required
- default + value
- values + value
- unsigned







models properties that can be set from metadata

- guarded
- fillable
- hidden
- primary_key
- incrementing
- keyType
- $timestamps (created_at + updated_at)
- attributes (default values)














- From the Blueprint
	- primary
	- unique
	- index
	- increments (+ the likes)
		- rules: numeric, min:0, max
		- cast: int
	- char, string
		- rules: string, max chars
	- text + the likes
		- rules: string, max (in bytes)
	- integer + likes
		- rules: numeric, min, max, 
		- cast: int
	- float, double
		- rules: float, min, max
		- cast: float
	- decimal
		- rules: string, min, max
		- cast: none
	- boolean
		- rules: boolean
		- cast: boolean
	- enum, set
		- rules: string, in
	- json, jsonb
		- rules: json ?
	- date, datetime, year, time
		- rule: date
		- cast: datetime
	- timestamp
		- cast: int or datetime
	- binary
	- uuid
		- rule: string:36


- From the column definition
	- `after` => `$column`
		- _
	- `first`
		- _
	- `autoIncrement`
		- numeric, min 0
	- `charset` => `$charset`
		- string
	- `collation` =>`$collation`
		- string
	- `comment` => `$comment`
		- _
	- `default` => `$value`
		- type of default value gives type
		- nullable rule
	
	- `generatedAs` => `$expression` (optional)
		- _
	- `always`
		- _
	- `nullable`
		- rule nullable
	- `primary`
		- gives the primary key
	- `spatialIndex`
		- _
	- `storedAs` => `$expression`
		- _
	- `index` => `$indexName` (optional)
		- _ (or the column is important)
	- `unique`
		- unique rule
	- `unsigned`
		- rules : numeric min:0
	- `useCurrent`
		- numeric ?
		- cast datetime or int
	- `virtualAs` => `$expression`
		- _
	- `persisted`



- from validation rules
	- Accepted
		- boolean
	- Active URL
		- string (url)
	- After (Date) / After Or Equal (Date)
		- cd: date, dt, timestamp
		- rule: date
	- Alpha / Alpha Dash / Alpha Numeric
		- string
	- Array
		- field is set, or json
	- Bail ?
	- Before (Date) / Before Or Equal (Date)
	- Between (min/max)
		-> numeric or string
	- Boolean
	- Confirmed ?
	- Date
	- Date Equals (Date)
	- Date Format (Date)
	- Different ?
	- Digits / Digits Between
		- numeric, float
	- Dimensions (Image Files) ?
	- Distinct ?
	- E-Mail
		- string
	- Ends With (value)
		- string
	- Exclude If ?
	- Exclude Unless ?
	- Exists (Database)
		- relation
	- File ?
	- Filled ? 
	- Greater Than / Greater Than Or Equal
		-  string, muneric
	- Image (File)
	- In
	- In Array
	- Integer
	- IP Address
	- JSON
	- Less Than
	- Less Than Or Equal
	- Max
	- MIME Types
	- MIME Type By File Extension
	- Min
	- Not In
	- Not Regex
	- Nullable
	- Numeric
	- Password
	- Present
	- Regular Expression
	- Required
	- Required If
	- Required Unless
	- Required With
	- Required With All
	- Required Without
	- Required Without All
	- Same
	- Size
	- Sometimes
	- Starts With
	- String
	- Timezone
	- Unique (Database)
	- URL
	- UUID
