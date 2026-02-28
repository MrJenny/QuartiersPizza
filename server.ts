import express from "express";
import { createServer as createViteServer } from "vite";
import Database from "better-sqlite3";
import path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const db = new Database("pizza_oven.db");

// Initialize database
db.exec(`
  CREATE TABLE IF NOT EXISTS bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    date TEXT NOT NULL,
    startTime TEXT NOT NULL,
    endTime TEXT NOT NULL,
    notes TEXT,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
  )
`);

async function startServer() {
  const app = express();
  const PORT = 3000;

  app.use(express.json());

  // API routes
  app.get("/api/bookings", (req, res) => {
    try {
      const bookings = db.prepare("SELECT * FROM bookings ORDER BY date ASC, startTime ASC").all();
      res.json(bookings);
    } catch (error) {
      res.status(500).json({ error: "Failed to fetch bookings" });
    }
  });

  app.post("/api/bookings", (req, res) => {
    const { name, date, startTime, endTime, notes } = req.body;
    
    if (!name || !date || !startTime || !endTime) {
      return res.status(400).json({ error: "Missing required fields" });
    }

    try {
      // Check for overlaps
      const overlap = db.prepare(`
        SELECT * FROM bookings 
        WHERE date = ? 
        AND (
          (startTime <= ? AND endTime > ?) OR
          (startTime < ? AND endTime >= ?) OR
          (? <= startTime AND ? > startTime)
        )
      `).get(date, startTime, startTime, endTime, endTime, startTime, endTime);

      if (overlap) {
        return res.status(400).json({ error: "This time slot is already booked" });
      }

      const info = db.prepare(
        "INSERT INTO bookings (name, date, startTime, endTime, notes) VALUES (?, ?, ?, ?, ?)"
      ).run(name, date, startTime, endTime, notes);
      
      res.json({ id: info.lastInsertRowid });
    } catch (error) {
      console.error(error);
      res.status(500).json({ error: "Failed to create booking" });
    }
  });

  app.delete("/api/bookings/:id", (req, res) => {
    try {
      db.prepare("DELETE FROM bookings WHERE id = ?").run(req.params.id);
      res.json({ success: true });
    } catch (error) {
      res.status(500).json({ error: "Failed to delete booking" });
    }
  });

  // Vite middleware for development
  if (process.env.NODE_ENV !== "production") {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: "spa",
    });
    app.use(vite.middlewares);
  } else {
    app.use(express.static(path.join(__dirname, "dist")));
    app.get("*", (req, res) => {
      res.sendFile(path.join(__dirname, "dist", "index.html"));
    });
  }

  app.listen(PORT, "0.0.0.0", () => {
    console.log(`Server running on http://localhost:${PORT}`);
  });
}

startServer();
