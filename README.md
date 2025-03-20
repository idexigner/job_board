# Job Board API with Advanced Filtering

A Laravel/Lumen-based Job Board API featuring advanced filtering capabilities similar to Airtable, implementing Entity-Attribute-Value (EAV) design patterns alongside traditional relational database models.

## Features

- Advanced filtering system with support for multiple operators
- Entity-Attribute-Value (EAV) implementation for dynamic job attributes
- Many-to-many relationships for languages, locations, and categories
- Efficient database indexing for optimized queries
- Rate limiting and caching for better performance
- Comprehensive error handling

## Tech Stack

- PHP 8.0+
- Laravel/Lumen Framework
- MySQL 5.7+
- Composer

## Installation

1. Clone the repository
```bash
git clone https://github.com/idexigner/job_board.git
cd job-board-api
```

2. Install dependencies
```bash
composer install
```

3. Environment setup
```bash
cp .env.example .env
# Update database credentials in .env file
```

4. Database setup
```bash
# Option 1: Run migrations and seeders
php artisan migrate
php artisan db:seed

# Option 2: Import sample database
mysql -u your_username -p your_database < database/sample/job_board.sql
```

## Sample Data

The project includes sample data that can be loaded in two ways:

1. Using seeders:
   - LanguageSeeder: Adds programming languages
   - LocationSeeder: Adds job locations
   - CategorySeeder: Adds job categories
   - AttributeSeeder: Adds EAV attributes
   - JobSeeder: Adds sample jobs with relationships

2. Using sample database:
   - A complete sample database is provided at `database/sample/job_board.sql`
   - Contains 400+ job listings with various attributes and relationships

## API Documentation

Detailed API documentation is available in [API.md](API.md), covering:
- Endpoint specifications
- Filter syntax and examples
- Complex query examples
- Error handling

## Postman Collection

1. Import the Postman collection:
   - File: `postman/Job_Board_API.postman_collection.json`
   - Environment: `postman/Job_Board_API.postman_environment.json`

2. Update environment variables:
   - `base_url`: Your API base URL
   - `api_key`: If you implement authentication

## Design Decisions, Assumptions, and Trade-offs

### Architecture Decisions
1. **EAV Model Implementation**
   - Chose EAV (Entity-Attribute-Value) pattern for flexible job attributes
   - Trade-off: Increased query complexity for better schema flexibility
   - Assumption: Job attributes will vary significantly across different job types

2. **Query Parser Design**
   - Custom query parser implementation for complex filtering
   - Trade-off: Development time vs using existing solutions
   - Assumption: Need for highly customized filtering logic justifies custom implementation

3. **Database Design**
   - Separate tables for core job data and EAV attributes
   - Composite indexes on frequently queried combinations
   - Trade-off: Storage space vs query performance
   - Assumption: Read operations are more frequent than writes

4. **Caching Strategy**
   - Cache job IDs instead of full query results
   - Implemented at service layer with configurable TTL
   - Trade-off: Memory usage vs response time
   - Assumption: Job data changes less frequently than it's queried

5. **Performance Optimizations**
   - Limited index lengths for text fields (191 characters)
   - Recommended limit of 5 concurrent filter conditions
   - Trade-off: Query flexibility vs performance
   - Assumption: Most queries will use common filter combinations

6. **API Design**
   - RESTful approach with query parameter-based filtering
   - Consistent error response format
   - Trade-off: URL length limitations vs query complexity
   - Assumption: Frontend will handle complex query building

7. **Security Considerations**
   - Rate limiting on API endpoints
   - SQL injection prevention in filter parser
   - No authentication for job listings (read-only)
   - Trade-off: Accessibility vs security
   - Assumption: Public job listings don't require authentication

8. **Error Handling**
   - Graceful handling of invalid filters
   - Empty results for non-existent attributes
   - Detailed validation messages
   - Trade-off: Response size vs error detail
   - Assumption: Detailed error messages aid in development and debugging

### Technical Limitations
1. Maximum query complexity (5 filter conditions)
2. Text field index length limited to 191 characters
3. URL length restrictions for complex queries
4. Cache memory consumption with large result sets

### Future Considerations
1. Potential implementation of GraphQL for complex queries
2. Elasticsearch integration for better text search
3. Caching layer optimization
4. Authentication for private job listings
5. API versioning strategy

## Testing

```bash
# Run unit tests
php artisan test

# Run specific test suite
php artisan test --filter=JobFilterTest
```

## Available Data

### Job Types
- full-time
- part-time
- contract
- freelance

### Categories
- Backend Development
- Frontend Development
- Full Stack Development
- DevOps
- etc.

### Languages
- PHP
- Python
- JavaScript
- Java
- etc.

### Locations
- New York
- San Francisco
- London
- etc.

### Attributes
- experience_years (number)
- educational_level (select)
- work_environment (select)
- requires_travel (boolean)
- certification_required (boolean)

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
