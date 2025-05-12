📝 WP Smart Popup Campaigns – PRD (Product Requirements Document)
📌 Overview
A simple, flexible WordPress plugin that lets admins create and manage popup campaigns using either images or custom HTML. Campaigns support page targeting, dismissal behavior, scheduling, and basic analytics (views and clicks).

🧩 Core Features

Campaign Management
Admins can create, edit, delete, and activate/deactivate popup campaigns.

Each campaign is stored as a custom post type (popup_campaign).

Popup Content Options
✅ Content Type Selector:

Image: Upload or select from media library

HTML: Enter custom HTML (e.g., form, video embed, styled text)

If Image is selected:

Must provide redirect URL

Option to open link in same or new tab

If HTML is selected:

No redirect is required (can include CTA buttons, embedded forms, etc.)

Page Targeting
Choose where the popup appears:

All pages

Selected pages (multi-select dropdown with search)

Dismiss Behavior
Options for close button behavior:

Hide forever (per browser via cookie)

Reappear after X days

Scheduling
Set campaign start and (optional) end date.

Campaign Status
Active / Inactive toggle

Display Logic
Inject popup code into wp_footer.

Checks:

Campaign status

Page matching

Schedule window

Cookie (dismissal logic)

Renders popup based on selected content type.

📊 Campaign Analytics
Metrics Tracked:
Views: Incremented on each popup display

Clicks:

For image popups: click on image

For HTML popups: allow optional tracking via data-popup-click attribute

Stored in post_meta:
_popup_views

_popup_clicks

Admin Display:
Shown in both:

Campaign list (views, clicks, CTR)

Campaign edit screen (detailed stats)

🖥️ Admin UI – Create/Edit Campaign
Field	Type	Description
Campaign Name	Text	Internal name
Content Type	Dropdown: Image / HTML	Select the popup content mode
Image Upload	Media Upload (conditional)	Show if Content Type is Image
Redirect URL	Text (URL) (conditional)	Required if image is selected
Open In	Same / New Tab (conditional)	Behavior for redirect
Custom HTML	Rich text/HTML editor	Show if Content Type is HTML
Target Pages	All / Specific Pages	Page display logic
Page Selector	Multi-select dropdown	Visible when "Specific Pages" is selected
Close Behavior	Dropdown	Never show again / Show after X days
Show Again After Days	Number	Applies if "Show after X days" is selected
Schedule	Date Pickers	Start and (optional) end dates
Status	Toggle	Active / Inactive
Analytics (read-only)	Auto-updated	Views, Clicks, CTR

🧠 Technical Implementation Notes
Use wp_footer to inject popup container and dynamic logic.

For HTML popups, sanitize carefully and store as post meta.

Allow adding data-popup-click to HTML elements to count clicks (e.g., on CTA buttons).

Store view and click counts via AJAX to avoid reload dependency.

Dismissal logic handled via JavaScript and cookies.

create this plugin