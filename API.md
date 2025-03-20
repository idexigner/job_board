# Job Board API Documentation

## Base URL
```
http://your-domain/api
```

## Endpoints

### 1. Get Jobs with Filters
```
GET /jobs
```

#### Query Parameters
- `filter` (optional): String containing filter conditions
- `page` (optional): Page number for pagination (default: 1)
- `per_page` (optional): Number of items per page (default: 20)

#### Response Format
```json
{
    "success": true,
    "data": [...],
    "meta": {
        "current_page": 1,
        "last_page": 10,
        "per_page": 20,
        "total": 200
    }
}
```

## Filtering Syntax

### 1. Basic Filtering

#### Text/String Fields
```
field=value
field LIKE value
```

Examples:
```
title LIKE Senior
company_name=Google
description LIKE Python
```

#### Numeric Fields
```
field>=value
field<=value
field=value
field>value
field<value
```

Examples:
```
salary_min>=50000
salary_max<=150000
```

#### Boolean Fields
```
field=true
field=false
```

Examples:
```
is_remote=true
```

#### Enum Fields
```
field=value
```

Examples:
```
job_type=full-time
status=published
```

### 2. Relationship Filtering

#### Languages
```
languages HAS_ANY (Python,JavaScript)
languages IS_ANY (Python,JavaScript)
languages EXISTS
languages = (Python,JavaScript)
```

#### Locations
```
locations HAS_ANY (New York,San Francisco)
locations IS_ANY (New York,San Francisco)
locations EXISTS
locations = (New York)
```

#### Categories
```
categories HAS_ANY (Full Stack Development,Backend Development)
categories IS_ANY (Data Science)
categories EXISTS
categories = (Backend Development)
```

### 3. EAV (Entity-Attribute-Value) Filtering

#### Number Attributes
```
attribute:experience_years>=5
attribute:experience_years<=3
attribute:experience_years=2
```

#### Select Attributes
```
attribute:education_level=["High School"]
attribute:education_level=["Bachelor\'s Degree","Master\'s Degree"]
attribute:work_environment=["Remote","Hybrid"]
```

#### Boolean Attributes
```
attribute:requires_travel=true
attribute:certification_required=false
```

### 4. Logical Operators

#### AND Operator (default)
```
condition1 AND condition2
```

#### OR Operator
```
(condition1 OR condition2)
```

#### Complex Grouping
```
(condition1 OR condition2) AND condition3
```

## Complex Query Examples

### 1. Senior Full Stack Developer in New York or Remote
```
GET /api/jobs?filter=title LIKE Senior AND categories = (Full Stack Development) AND (locations = (New York) OR is_remote=true)
```

### 2. Data Science Jobs with Python and 5+ Years Experience
```
GET /api/jobs?filter=categories = (Data Science) AND languages = (Python) AND attribute:experience_years>=5
```

### 3. High-Paying Remote Jobs with Multiple Languages
```
GET /api/jobs?filter=salary_min>=150000 AND is_remote=true AND languages HAS_ANY (JavaScript,Python,Java) AND attribute:work_environment=["Remote"]
```

### 4. Entry-Level Backend Jobs with Education Requirements
```
GET /api/jobs?filter=categories = (Backend Development) AND attribute:education_level=["Bachelor\'s Degree"] AND attribute:experience_years<=2 AND languages HAS_ANY (Java,Python,PHP)
```

### 5. Jobs with Multiple Conditions
```
GET /api/jobs?filter=(languages HAS_ANY (Python,Java) OR languages HAS_ANY (JavaScript)) AND (locations = (New York) OR is_remote=true) AND salary_min>=100000
```

### 6. Jobs with Multiple Attributes
```
GET /api/jobs?filter=attribute:experience_years>=5 AND attribute:education_level=["Master\'s Degree"] AND attribute:work_environment=["Remote"] AND attribute:requires_travel=false
```

### 7. Jobs with Mixed Filters
```
GET /api/jobs?filter=job_type=full-time AND (languages HAS_ANY (Python,JavaScript)) AND (locations IS_ANY (New York,Remote)) AND attribute:experience_years>=3 AND attribute:certification_required=true
```

## Error Responses

### Invalid Filter Format
```json
{
    "success": false,
    "message": "Invalid filter format",
    "error": "Error message details"
}
```

### Database Error
```json
{
    "success": false,
    "message": "Error executing database query",
    "error": "Invalid filter criteria or database error"
}
```

## Notes
1. All filter values are case-sensitive
2. Boolean values can be specified as `true`/`false` or `1`/`0`
3. Select attributes require JSON array format with quotes
4. Relationship filters support multiple values in parentheses
5. Complex queries can be built by combining different filter types with logical operators
6. The API supports pagination with a default of 20 items per page

## Available Data

### Job Types
- full-time
- part-time
- contract
- freelance

### Status
- draft
- published
- archived

### Categories
- Backend Development
- Blockchain Development
- Cloud Computing
- Data Science
- Database Administration
- DevOps
- Frontend Development
- Full Stack Development
- Machine Learning
- Mobile Development
- Product Management
- QA Engineering
- Security
- Systems Architecture
- UI/UX Design

### Languages
- C#
- C++
- Dart
- Go
- Java
- JavaScript
- Kotlin
- PHP
- Python
- R
- Ruby
- Rust
- Scala
- Swift
- TypeScript

### Locations
- Austin
- Berlin
- Boston
- London
- New York
- San Francisco
- Seattle
- Singapore
- Sydney
- Toronto

### Attributes
- experience_years (number)
- education_level (select)
  - High School
  - Bachelor\'s Degree
  - Master\'s Degree
  - PhD
  - Other
- work_environment (select)
  - Office
  - Hybrid
  - Remote
- requires_travel (boolean)
- certification_required (boolean) 
