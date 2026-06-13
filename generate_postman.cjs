const fs = require('fs');

const collection = {
  info: {
    name: "ITI System API",
    schema: "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  variable: [
    { key: "base_url", value: "http://localhost:8000/api", type: "string" },
    { key: "token", value: "", type: "string" }
  ],
  item: [
    {
      name: "1. Auth (READY)",
      item: [
        {
          name: "Login",
          event: [{ listen: "test", script: { exec: ["var jsonData = pm.response.json();", "pm.collectionVariables.set('token', jsonData.token);"] } }],
          request: { method: "POST", url: "{{base_url}}/auth/login", body: { mode: "raw", raw: '{"email":"manager@iti.test","password":"password"}', options: { raw: { language: "json" } } } }
        },
        {
          name: "Get Profile",
          request: { method: "GET", url: "{{base_url}}/auth/me" }
        }
      ]
    },
    {
      name: "2. Sessions (READY)",
      item: [
        { name: "List Sessions", request: { method: "GET", url: "{{base_url}}/engagements/1/sessions" } },
        { name: "Create Session", request: { method: "POST", url: "{{base_url}}/engagements/1/sessions", body: { mode: "raw", raw: '{"session_date":"2026-06-09"}', options: { raw: { language: "json" } } } } },
        { name: "Deliver Session", request: { method: "PATCH", url: "{{base_url}}/sessions/1/deliver" } }
      ]
    },
    {
      name: "3. User & Track Setup (READY)",
      item: [
        { name: "Create User", request: { method: "POST", url: "{{base_url}}/users", body: { mode: "raw", raw: '{"name":"New User","email":"test@iti.test","password":"password","password_confirmation":"password","role":"instructor","compensation_type":"internal"}', options: { raw: { language: "json" } } } } },
        { name: "Create Track", request: { method: "POST", url: "{{base_url}}/tracks", body: { mode: "raw", raw: '{"name":"Web Dev","code":"WD","description":"Web Track"}', options: { raw: { language: "json" } } } } }
      ]
    },
    {
      name: "4. Student Tags & Announcements (WAEL - READY)",
      item: [
        { name: "Tag Student", request: { method: "POST", url: "{{base_url}}/students/1/tags", body: { mode: "raw", raw: '{"tag_type":"predefined", "tag_value":"uses AI", "note":"Did a great job"}', options: { raw: { language: "json" } } } } },
        { name: "List Tags", request: { method: "GET", url: "{{base_url}}/students/1/tags" } },
        { name: "Post Announcement", request: { method: "POST", url: "{{base_url}}/cohorts/1/announcements", body: { mode: "raw", raw: '{"title":"Exam Tomorrow", "body":"Don\'t be late"}', options: { raw: { language: "json" } } } } },
        { name: "List Announcements", request: { method: "GET", url: "{{base_url}}/cohorts/1/announcements" } }
      ]
    },
    {
      name: "5. Analytics & Billing (WAEL - READY)",
      item: [
        { name: "Student Analytics", request: { method: "GET", url: "{{base_url}}/analytics/student" } },
        { name: "At-Risk Analytics", request: { method: "GET", url: "{{base_url}}/analytics/at-risk" } },
        { name: "View Billing", request: { method: "GET", url: "{{base_url}}/billing" } }
      ]
    },
    {
      name: "6. QR Attendance (WAEL - READY)",
      item: [
        { name: "Generate QR", request: { method: "GET", url: "{{base_url}}/qr/session/1" } },
        { name: "Scan QR", request: { method: "POST", url: "{{base_url}}/qr/scan", body: { mode: "raw", raw: '{"session_id": 1, "payload": "paste-payload-here"}', options: { raw: { language: "json" } } } } }
      ]
    }
  ]
};

// Add auth header to all requests that don't have it explicitly set yet
collection.item.forEach(folder => {
  folder.item.forEach(req => {
    if (!req.request.auth) {
      req.request.auth = { type: "bearer", bearer: [{ key: "token", value: "{{token}}", type: "string" }] };
    }
  });
});

fs.writeFileSync('postman_collection.json', JSON.stringify(collection, null, 2));
console.log('✅ postman_collection.json generated successfully!');
