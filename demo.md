<style>
    /* VS Code Markdown Preview - Elite Database Styling */
    table {
        width: 100% !important;
        display: table !important;
        border-collapse: collapse !important;
        border: 2px solid #333 !important;
        margin-bottom: 30px !important;
        background-color: #fdfdfd !important;
    }
    th {
        background-color: #d1d1d1 !important; /* Original Gray Header */
        border: 1px solid #333 !important;
        padding: 12px !important;
        text-align: left !important;
        color: #000 !important;
        font-weight: bold !important;
        text-transform: uppercase !important;
    }
    td {
        border: 1px solid #999 !important;
        padding: 10px !important;
        color: #333 !important;
    }
    tr:nth-child(even) {
        background-color: #f2f2f2 !important;
    }
    h3 {
        color: #333 !important;
        border-bottom: 2px solid #d1d1d1 !important;
        padding-bottom: 5px !important;
        margin-top: 40px !important;
    }
</style>

# LaraBids Project - Detailed Database Schema

This document contains the detailed database structure for the **LaraBids** project, following a tabular format for clear understanding of fields, constraints, and data types.

---

### 1) User
**Description**: Stores the personal and account details of all users (Bidders, Sellers, and Admins).

| FieldName             | Datatype  | Size | Constraint         | Description                                           |
| :---                  | :---      | :--- | :---               | :---                                                  |
| **id**                | BigInt    | 20   | Primary Key        | Unique identification number for each user.           |
| **name**              | Varchar   | 255  | Not Null           | Full name of the user.                                |
| **username**          | Varchar   | 255  | Unique             | Unique handle/username for the user.                  |
| **email**             | Varchar   | 255  | Not Null, Unique   | Registered email address for login.                   |
| **phone**             | Varchar   | 20   | Nullable           | Contact mobile number.                                |
| **location**          | Varchar   | 255  | Nullable           | Physical address or city.                             |
| **avatar**            | Varchar   | 255  | Nullable           | Profile image path.                                   |
| **bio**               | Text      | -    | Nullable           | Brief biography.                                      |
| **password**          | Varchar   | 255  | Not Null           | Hashed password.                                      |
| **created_by**        | BigInt    | 20   | Foreign Key        | ID of the admin who created this user (if any).       |
| **google_id**         | Varchar   | 255  | Unique/Nullable    | OAuth provider ID for social login.                   |
| **email_verified_at** | Timestamp | -    | Nullable           | Date/Time of email verification.                      |
| **remember_token**    | Varchar   | 100  | Nullable           | Token for persistent login sessions.                  |
| **deleted_at**        | Timestamp | -    | Nullable           | Soft delete timestamp.                                |
| **created_at**        | Timestamp | -    | Not Null           | Registration Date.                                    |
| **updated_at**        | Timestamp | -    | Not Null           | Profile update tracking.                               |

---

### 2) Category
**Description**: Stores categories and sub-categories to organize auction items.

| FieldName     | Datatype  | Size | Constraint         | Description                                           |
| :---          | :---      | :--- | :---               | :---                                                  |
| **id**        | BigInt    | 20   | Primary Key        | Unique identification number for each category.       |
| **parent_id** | BigInt    | 20   | Foreign Key        | ID of the parent category (for sub-categories).       |
| **name**      | Varchar   | 255  | Not Null           | Name of the category (e.g., Electronics).             |
| **slug**      | Varchar   | 255  | Not Null, Unique   | URL-friendly version of the name.                     |
| **icon**      | Varchar   | 100  | Nullable           | FontAwesome class name for the category icon.         |
| **is_active** | Boolean   | -    | Not Null           | Status to toggle visibility (True/False).             |
| **deleted_at**| Timestamp | -    | Nullable           | Soft delete support.                                  |
| **timestamps**| Timestamp | -    | Not Null           | Standard created/updated time tracking.               |

---

### 3) Auction
**Description**: The central table containing all auction listing details.

| FieldName         | Datatype | Size | Constraint       | Description                                         |
| :---              | :---     | :--- | :---             | :---                                                |
| **id**            | BigInt   | 20   | Primary Key      | Unique identification number for the auction.       |
| **user_id**       | BigInt   | 20   | Foreign Key      | ID of the seller who posted the auction.            |
| **category_id**   | BigInt   | 20   | Foreign Key      | ID of the linked category.                          |
| **winner_id**     | BigInt   | 20   | Foreign Key      | ID of the user who won the auction.                 |
| **location**      | Varchar  | 255  | Nullable         | Physical location of the item/auction.              |
| **title**         | Varchar  | 255  | Not Null         | Short name/title of the item.                       |
| **description**   | Text     | -    | Not Null         | Detailed description of the item.                   |
| **starting_price**| Decimal  | 16,2 | Not Null         | The initial price when bidding starts.              |
| **reserve_price** | Decimal  | 12,2 | Nullable         | Minimum price seller is willing to accept.          |
| **current_price** | Decimal  | 16,2 | Not Null         | The latest highest bid amount.                      |
| **min_increment** | Decimal  | 16,2 | Default: 1.0     | Minimum amount by which a bid must increase.        |
| **image**         | Varchar  | 255  | Nullable         | Path to the main item thumbnail.                    |
| **document**      | Varchar  | 255  | Nullable         | Supporting documents/verifications.                 |
| **specifications**| JSON     | -    | Nullable         | Detailed technical specifications.                  |
| **start_time**    | DateTime | -    | Not Null         | Date and time when bidding begins.                  |
| **end_time**      | DateTime | -    | Not Null         | Date and time when bidding ends.                    |
| **status**        | Enum     | -    | Not Null         | [draft, active, closed, cancelled].                 |
| **cancel_reason** | Text     | -    | Nullable         | Reason provided if cancelled.                       |
| **deleted_at**    | Timestamp| -    | Nullable         | Soft delete support.                                |
| **timestamps**    | Timestamp| -    | Not Null         | Standard tracking.                                  |

---

### 4) Bid
**Description**: Records every single bid placed by users.

| FieldName     | Datatype  | Size | Constraint         | Description                                           |
| :---          | :---      | :--- | :---               | :---                                                  |
| **id**        | BigInt    | 20   | Primary Key        | Unique identification number for the bid.             |
| **auction_id**| BigInt    | 20   | Foreign Key        | The auction on which the bid is placed.               |
| **user_id**   | BigInt    | 20   | Foreign Key        | The user who placed the bid.                          |
| **amount**    | Decimal   | 16,2 | Not Null           | The bid amount.                                       |
| **created_at**| Timestamp | -    | Not Null           | Timestamp of when the bid was placed.                 |

---

### 5) Payment
**Description**: Stores details of successful auction payouts via PayU integration.

| FieldName         | Datatype  | Size | Constraint         | Description                                           |
| :---              | :---      | :--- | :---               | :---                                                  |
| **id**            | BigInt    | 20   | Primary Key        | Unique ID for the payment record.                     |
| **user_id**       | BigInt    | 20   | Foreign Key        | The winner who made the payment.                      |
| **auction_id**    | BigInt    | 20   | Foreign Key        | The auction being paid for.                           |
| **txnid**         | Varchar   | 255  | Unique, Not Null   | Unique Transaction ID generated for PayU.             |
| **amount**        | Decimal   | 15,2 | Not Null           | The final auction amount paid.                        |
| **status**        | Varchar   | 50   | Default: pending   | [pending, success, failed].                           |
| **payu_id**       | Varchar   | 255  | Nullable           | PayU's internal `mihpayid` for tracking.              |
| **productinfo**   | Varchar   | 255  | Not Null           | Description of the item purchased.                    |
| **additional_data**| JSON     | -    | Nullable           | Stores raw responses from the gateway.                |
| **timestamps**    | Timestamp | -    | Not Null           | creation and last update tracking.                    |

---

### 6) Review / Feedback
**Description**: Stores feedback for completed auctions.

| FieldName     | Datatype  | Size | Constraint         | Description                                           |
| :---          | :---      | :--- | :---               | :---                                                  |
| **id**        | BigInt    | 20   | Primary Key        | Unique ID for the review.                             |
| **auction_id**| BigInt    | 20   | Foreign Key        | The auction being reviewed.                           |
| **from_user** | BigInt    | 20   | Foreign Key        | User ID of the reviewer.                              |
| **to_user**   | BigInt    | 20   | Foreign Key        | User ID of the recipient.                             |
| **rating**    | Integer   | 1    | Not Null           | Star rating (1-5).                                    |
| **comment**   | Text      | -    | Nullable           | Feedback text.                                        |
| **created_at**| Timestamp | -    | Not Null           | Feedback timing.                                      |

---

### 7) Watchlist
**Description**: Bookmarked items followed by users.

| FieldName     | Datatype  | Size | Constraint         | Description                                           |
| :---          | :---      | :--- | :---               | :---                                                  |
| **id**        | BigInt    | 20   | Primary Key        | Unique mapping ID.                                    |
| **user_id**   | BigInt    | 20   | Foreign Key        | The user following the item.                          |
| **auction_id**| BigInt    | 20   | Foreign Key        | The auction item being followed.                      |
| **created_at**| Timestamp | -    | Not Null           | Added on timestamp.                                   |

---

### 8) Notification
**Description**: System-generated alerts.

| FieldName     | Datatype  | Size | Constraint         | Description                                           |
| :---          | :---      | :--- | :---               | :---                                                  |
| **id**        | BigInt    | 20   | Primary Key        | Unique ID for notification.                           |
| **user_id**   | BigInt    | 20   | Foreign Key        | Recipient of the alert.                               |
| **title**     | Varchar   | 255  | Not Null           | Short summary of the alert.                           |
| **message**   | Text      | -    | Not Null           | Full notification content.                            |
| **is_read**   | Boolean   | -    | Default: False     | Read status.                                          |
| **timestamps**| Timestamp | -    | Not Null           | Time tracking.                                        |

---

### 9) FAQ / Support (Contacts)
**Description**: Support form queries and contact messages.

| FieldName     | Datatype  | Size | Constraint         | Description                                           |
| :---          | :---      | :--- | :---               | :---                                                  |
| **id**        | BigInt    | 20   | Primary Key        | Message ID.                                           |
| **name**      | Varchar   | 255  | Not Null           | Sender name.                                          |
| **email**     | Varchar   | 255  | Not Null           | Sender email for replies.                             |
| **subject**   | Varchar   | 255  | Not Null           | Topic of inquiry.                                     |
| **message**   | Text      | -    | Not Null           | Inquiry message.                                      |
| **status**    | Enum      | -    | Default: unread    | [unread, read, replied].                              |
| **admin_notes**| Text      | -    | Nullable           | Notes by admin regarding this message.                |
| **replied_by**| BigInt    | 20   | Foreign Key        | Admin ID who replied to this message.                 |
| **deleted_at**| Timestamp | -    | Nullable           | Soft delete support.                                  |
| **timestamps**| Timestamp | -    | Not Null           | Time tracking.                                        |

---

### 10) Auto Bids
**Description**: Configuration for Proxy Bidding (Auto-Bid).

| FieldName       | Datatype | Size | Constraint       | Description                                         |
| :---            | :---     | :--- | :---             | :---                                                |
| **id**          | BigInt   | 20   | Primary Key      | Rule ID.                                            |
| **auction_id**  | BigInt   | 20   | Foreign Key      | Targeted auction.                                   |
| **user_id**     | BigInt   | 20   | Foreign Key      | User who set the limit.                             |
| **max_bid_amount**| Decimal| 16,2 | Not Null         | Maximum budget limit.                               |
| **active**      | Boolean  | -    | Default: True    | Status of the proxy.                                |
| **timestamps**  | Timestamp| -    | Not Null         | Timing tracking.                                    |

---

### 11) Auction Images
**Description**: Image Gallery for items.

| FieldName     | Datatype  | Size | Constraint         | Description                                           |
| :---          | :---      | :--- | :---               | :---                                                  |
| **id**        | BigInt    | 20   | Primary Key        | Mapping ID.                                           |
| **auction_id**| BigInt    | 20   | Foreign Key        | Related auction.                                      |
| **image_path**| Varchar   | 255  | Not Null           | Storage path.                                         |
| **sort_order**| Integer   | -    | Default: 0         | Sequence order.                                       |
| **is_primary**| Boolean   | -    | Not Null           | Main thumbnail flag.                                  |
| **timestamps**| Timestamp | -    | Not Null           | Time tracking.                                        |

---

### 12) Testimonials
**Description**: User reviews for landing page.

| FieldName     | Datatype  | Size | Constraint         | Description                                           |
| :---          | :---      | :--- | :---               | :---                                                  |
| **id**        | BigInt    | 20   | Primary Key        | Mapping ID.                                           |
| **name**      | Varchar   | 255  | Not Null           | Name of reviewer.                                     |
| **role**      | Varchar   | 255  | Nullable           | Designation.                                          |
| **content**   | Text      | -    | Not Null           | Review message.                                       |
| **avatar_url**| Varchar   | 255  | Nullable           | Image link.                                           |
| **is_active** | Boolean   | -    | Default: True      | Frontend visibility.                                  |
| **timestamps**| Timestamp | -    | Not Null           | Timing tracking.                                      |

---

### 13) KYC (Know Your Customer)
**Description**: Stores User identity verification details.

| FieldName           | Datatype | Size | Constraint       | Description                                         |
| :---                | :---     | :--- | :---             | :---                                                |
| **id**              | BigInt   | 20   | Primary Key      | Unique KYC Record ID.                               |
| **user_id**         | BigInt   | 20   | Foreign Key      | Linked User account.                                |
| **full_name**       | Varchar  | 255  | Not Null         | As mentioned on ID document.                        |
| **date_of_birth**   | Date     | -    | Not Null         | User's birth date.                                  |
| **gender**          | Varchar  | 255  | Nullable         | User's gender.                                      |
| **id_type**         | Enum     | -    | Not Null         | [aadhaar, pan, passport, driving_license].          |
| **id_number**       | Varchar  | 255  | Not Null         | Document number for verification.                   |
| **id_document**     | Varchar  | 255  | Not Null         | Path to uploaded ID scan.                           |
| **selfie_image**    | Varchar  | 255  | Not Null         | Path to user's selfie image.                        |
| **signature_image** | Varchar  | 255  | Nullable         | Electronic or scanned signature.                    |
| **status**          | Enum     | -    | Default: pending | [pending, approved, rejected].                      |
| **admin_note**      | Text     | -    | Nullable         | Rejection reason or internal notes.                 |
| **timestamps**      | Timestamp| -    | Not Null         | creation and last update tracking.                  |

---

### 14) Auction Registrations
**Description**: Tracks which users have registered for specific auctions.

| FieldName     | Datatype  | Size | Constraint         | Description                                           |
| :---          | :---      | :--- | :---               | :---                                                  |
| **id**        | BigInt    | 20   | Primary Key        | Mapping ID.                                           |
| **user_id**   | BigInt    | 20   | Foreign Key        | Registered User.                                      |
| **auction_id**| BigInt    | 20   | Foreign Key        | Target Auction Item.                                  |
| **status**    | Varchar   | 255  | Default: registered| Current registration status.                          |
| **timestamps**| Timestamp | -    | Not Null           | Time tracking.                                        |

---

### 15) Admin Interface (DataTables)
**Description**: Technical configuration for Yajra DataTables used in Payments & Auctions.

| Page              | Column            | Type           | Data Source          | Render Logic                                      |
| :---              | :---              | :---           | :---                 | :---                                              |
| **Payments**      | **Transaction**   | HTML/Custom    | `txnid`, `payu_id`   | Shows TXN ID with PayU ID as subtitle.            |
| **Payments**      | **Buyer Info**    | HTML/Custom    | `user.name`, `email` | Aggregates name and email into one cell.          |
| **Payments**      | **Auction**       | HTML/Custom    | `auction.title`      | Links to auction item.                            |
| **Payments**      | **Amount**        | Numeric        | `amount`             | Formatted with ₹ Currency.                        |
| **Payments**      | **Fee (5%)**      | Calculation    | `amount * 0.05`      | Platform commission cut.                          |
| **Payments**      | **Status**        | Badge/HTML     | `status`             | Color-coded badges (Green, Yellow, Red).          |
| **Auctions**      | **Image**         | Image/HTML     | `image`              | 50x50 object-fit thumb.                           |
| **Auctions**      | **Current Bid**   | Numeric        | `current_price`      | Live dynamic bids.                                |
| **Common**        | **Timestamp**     | Date/Time      | `created_at`         | Human-readable (d M, Y h:i A).                    |

---
