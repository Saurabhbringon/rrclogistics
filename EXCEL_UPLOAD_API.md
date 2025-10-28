# Customer Excel/CSV Upload API

This API allows you to upload Excel files (.xlsx, .xls) or CSV files containing customer data with the following fields:

-   Name
-   Email
-   Phone
-   Address

## API Endpoints

### 1. Upload Excel/CSV File

**POST** `/api/customers/upload-excel`

**Parameters:**

-   `excel_file` (file, required): Excel file (.xlsx, .xls) or CSV file (.csv) containing customer data

**File Format:**
The file should have the following column headers in the first row:

-   Name
-   Email
-   Phone
-   Address

**Example Request:**

```bash
# For Excel file
curl -X POST http://your-domain/api/customers/upload-excel \
  -F "excel_file=@customers.xlsx"

# For CSV file
curl -X POST http://your-domain/api/customers/upload-excel \
  -F "excel_file=@customers.csv"
curl -X POST http://your-domain/api/customers/upload-excel \
  -F "excel_file=@customers.xlsx"
```

**Success Response (200):**

```json
{
    "status": 200,
    "success": true,
    "message": "Import completed. 5 customers imported, 0 failed.",
    "data": {
        "total_rows": 5,
        "imported": 5,
        "failed": 0,
        "errors": []
    }
}
```

**Error Response (400):**

```json
{
    "status": 400,
    "success": false,
    "message": "Import completed. 3 customers imported, 2 failed.",
    "data": {
        "total_rows": 5,
        "imported": 3,
        "failed": 2,
        "errors": [
            {
                "row": 3,
                "errors": ["Invalid email format in row 3"]
            },
            {
                "row": 5,
                "errors": ["Customer with this email or phone already exists"]
            }
        ]
    }
}
```

### 2. Download Template

**GET** `/api/customers/download-template`

Downloads a sample CSV template that can be used as a reference for the Excel upload format.

**Example Request:**

```bash
curl -X GET http://your-domain/api/customers/download-template \
  -o customer_template.csv
```

## Validation Rules

-   **Name**: Required, string, max 255 characters
-   **Email**: Required, valid email format, max 255 characters, must be unique
-   **Phone**: Required, string, max 20 characters, must be unique
-   **Address**: Required, string, max 500 characters

## Notes

-   Maximum file size: 10MB
-   Supported formats: .xlsx, .xls, .csv
-   Duplicate customers (same email or phone) will be skipped
-   All validation errors are returned with specific row numbers
-   The API will process all rows and return a summary of successes and failures

## Sample Data Format

| Name         | Email                    | Phone       | Address                       |
| ------------ | ------------------------ | ----------- | ----------------------------- |
| John Doe     | john.doe@example.com     | +1234567890 | 123 Main St, City, State, ZIP |
| Jane Smith   | jane.smith@example.com   | +0987654321 | 456 Oak Ave, City, State, ZIP |
| Mike Johnson | mike.johnson@example.com | +1122334455 | 789 Pine Rd, City, State, ZIP |
