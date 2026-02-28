<!DOCTYPE html>
<html lang="gsw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quatiers-Pizza-Ofe</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Modern Babel -->
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <!-- React & Libraries -->
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    
    <style>
        :root {
            --color-brand-olive: #5A5A40;
            --color-brand-cream: #f5f5f0;
            --color-brand-terracotta: #b35a38;
        }
        body {
            font-family: sans-serif;
            background-color: var(--color-brand-cream);
            color: #1a1a1a;
            margin: 0;
        }
        h1, h2, h3 {
            font-family: serif;
        }
        .bg-brand-olive { background-color: var(--color-brand-olive); }
        .bg-brand-terracotta { background-color: var(--color-brand-terracotta); }
    </style>
</head>
<body>
    <div id="root">
        <div style="display: flex; justify-content: center; align-items: center; height: 100vh; font-family: sans-serif; color: #666;">
            <div style="text-align: center;">
                <p style="font-size: 1.2rem; margin-bottom: 10px;">Quatiers-Pizza-Ofe wird glade...</p>
                <p style="font-size: 0.8rem; color: #999;">Wenn das länger als 5 Sekunde gaht, lueg i d'Konsole (F12).</p>
            </div>
        </div>
    </div>

    <script type="text/babel">
        const { useState, useEffect } = React;

        function App() {
            const [bookings, setBookings] = useState([]);
            const [isModalOpen, setIsModalOpen] = useState(false);
            const [selectedDate, setSelectedDate] = useState(new Date());
            const [loading, setLoading] = useState(true);
            const [error, setError] = useState(null);

            const [formData, setFormData] = useState({
                name: '',
                date: new Date().toISOString().split('T')[0],
                startTime: '17:00',
                endTime: '19:00',
                notes: ''
            });

            useEffect(() => {
                fetchBookings();
            }, []);

            const fetchBookings = async () => {
                try {
                    const response = await fetch('api.php?action=bookings');
                    if (!response.ok) {
                        const errData = await response.json();
                        throw new Error(errData.error || 'Reservatione hend nöd chöne glade werde.');
                    }
                    const data = await response.json();
                    setBookings(data);
                } catch (err) {
                    console.error('Fetch Error:', err);
                    setError('Datebank-Fehler: Chönt nöd mit de API verbinde. Überprüef d\'config.php.');
                } finally {
                    setLoading(false);
                }
            };

            const handleBooking = async (e) => {
                e.preventDefault();
                setError(null);
                try {
                    const response = await fetch('api.php?action=bookings', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(formData)
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.error || 'Reservation fählgschlage');
                    
                    await fetchBookings();
                    setIsModalOpen(false);
                    setFormData({
                        name: '',
                        date: new Date().toISOString().split('T')[0],
                        startTime: '17:00',
                        endTime: '19:00',
                        notes: ''
                    });
                } catch (err) {
                    setError(err.message);
                }
            };

            const deleteBooking = async (id) => {
                if (!confirm('Bisch sicher, dass die Reservation wotsch lösche?')) return;
                try {
                    const response = await fetch('api.php?action=delete&id=' + id, { method: 'DELETE' });
                    if (!response.ok) throw new Error('Lösche fählgschlage');
                    await fetchBookings();
                } catch (err) {
                    setError('Reservation het nöd chöne glöscht werde');
                }
            };

            const formatDate = (date) => {
                const days = ['Sunntig', 'Mändig', 'Ziischtig', 'Mittwuch', 'Donnschtig', 'Friitig', 'Samschtig'];
                const months = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'Auguscht', 'September', 'Oktober', 'November', 'Dezember'];
                return `${days[date.getDay()]}, ${date.getDate()}. ${months[date.getMonth()]}`;
            };

            const getBookingsForDate = (date) => {
                const dateStr = date.toISOString().split('T')[0];
                return bookings.filter(b => b.date === dateStr);
            };

            const changeDate = (days) => {
                const newDate = new Date(selectedDate);
                newDate.setDate(newDate.getDate() + days);
                setSelectedDate(newDate);
                setFormData(prev => ({ ...prev, date: newDate.toISOString().split('T')[0] }));
            };

            return (
                <div className="min-h-screen">
                    <header className="relative h-[40vh] flex items-center justify-center overflow-hidden bg-brand-olive text-white">
                        <img src="https://picsum.photos/seed/pizza-oven/1920/1080?blur=2" className="absolute inset-0 w-full h-full object-cover opacity-40" />
                        <div className="relative z-10 text-center px-4">
                            <h1 className="text-5xl md:text-7xl font-light tracking-tight mb-2">Quatiers-Pizza-Ofe</h1>
                            <p className="text-lg md:text-xl opacity-90 max-w-2xl mx-auto">Zämecho, bache und teile. Reservier diis Plätzli a üsem Quatiers-Holzofe.</p>
                        </div>
                    </header>

                    <main className="max-w-5xl mx-auto px-4 py-12">
                        {error && (
                            <div className="mb-8 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
                                <p className="font-bold">Fehler:</p>
                                <p>{error}</p>
                            </div>
                        )}

                        <div className="flex flex-col md:flex-row items-center justify-between gap-6 mb-12">
                            <div className="flex items-center gap-4">
                                <button onClick={() => changeDate(-1)} className="p-2 hover:bg-stone-200 rounded-full transition-colors">←</button>
                                <div className="text-center min-w-[240px]">
                                    <h2 className="text-3xl font-medium">{formatDate(selectedDate)}</h2>
                                    <p className="text-stone-500 text-sm uppercase tracking-widest mt-1">Tagesplan</p>
                                </div>
                                <button onClick={() => changeDate(1)} className="p-2 hover:bg-stone-200 rounded-full transition-colors">→</button>
                            </div>

                            <button onClick={() => setIsModalOpen(true)} className="bg-brand-olive text-white px-8 py-4 rounded-full font-medium shadow-xl hover:bg-brand-olive/90 transition-all">
                                + Plätzli reserviere
                            </button>
                        </div>

                        <div className="grid gap-6">
                            {loading ? (
                                <div className="text-center py-20 opacity-50">Plan wird glade...</div>
                            ) : getBookingsForDate(selectedDate).length > 0 ? (
                                getBookingsForDate(selectedDate).map((booking) => (
                                    <div key={booking.id} className="bg-white p-6 rounded-3xl shadow-sm border border-stone-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                        <div className="flex items-start gap-6">
                                            <div className="flex flex-col items-center justify-center bg-stone-50 p-4 rounded-2xl min-w-[100px]">
                                                <span className="text-xs text-stone-400 font-bold uppercase mb-1">Ziit</span>
                                                <span className="text-xl font-medium">{booking.startTime.substring(0,5)}</span>
                                                <span className="text-xs text-stone-400">bis</span>
                                                <span className="text-xl font-medium">{booking.endTime.substring(0,5)}</span>
                                            </div>
                                            <div>
                                                <h3 className="text-2xl font-medium">{booking.name}</h3>
                                                {booking.notes && <p className="text-stone-500 mt-1 italic">"{booking.notes}"</p>}
                                            </div>
                                        </div>
                                        <button onClick={() => deleteBooking(booking.id)} className="p-3 text-stone-300 hover:text-red-500 transition-all">Lösche</button>
                                    </div>
                                ))
                            ) : (
                                <div className="text-center py-20 bg-stone-50 rounded-3xl border-2 border-dashed border-stone-200">
                                    <h3 className="text-xl text-stone-400">No kei Reservatione für hüt.</h3>
                                </div>
                            )}
                        </div>
                    </main>

                    {isModalOpen && (
                        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
                            <div className="absolute inset-0 bg-stone-900/60 backdrop-blur-sm" onClick={() => setIsModalOpen(false)}></div>
                            <div className="relative bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden">
                                <div className="bg-brand-olive p-8 text-white">
                                    <h2 className="text-3xl">Ofe reserviere</h2>
                                </div>
                                <form onSubmit={handleBooking} className="p-8 space-y-6">
                                    <div>
                                        <label className="block text-sm font-bold text-stone-500 uppercase mb-2">Diin Name</label>
                                        <input required type="text" className="w-full px-4 py-4 bg-stone-50 border-none rounded-2xl" value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} />
                                    </div>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <label className="block text-sm font-bold text-stone-500 uppercase mb-2">Startziit</label>
                                            <input required type="time" className="w-full px-4 py-4 bg-stone-50 border-none rounded-2xl" value={formData.startTime} onChange={e => setFormData({...formData, startTime: e.target.value})} />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-bold text-stone-500 uppercase mb-2">Endziit</label>
                                            <input required type="time" className="w-full px-4 py-4 bg-stone-50 border-none rounded-2xl" value={formData.endTime} onChange={e => setFormData({...formData, endTime: e.target.value})} />
                                        </div>
                                    </div>
                                    <div>
                                        <label className="block text-sm font-bold text-stone-500 uppercase mb-2">Notize</label>
                                        <textarea className="w-full p-4 bg-stone-50 border-none rounded-2xl min-h-[100px]" value={formData.notes} onChange={e => setFormData({...formData, notes: e.target.value})} />
                                    </div>
                                    <div className="flex gap-4">
                                        <button type="button" onClick={() => setIsModalOpen(false)} className="flex-1 py-4 border border-stone-200 rounded-2xl">Abbreche</button>
                                        <button type="submit" className="flex-1 py-4 bg-brand-terracotta text-white rounded-2xl">Bestätige</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    )}

                    <footer className="max-w-5xl mx-auto px-4 py-12 border-t border-stone-200 text-center text-stone-400">
                        <p className="font-serif italic text-lg mb-2">"E Gmeinschaft wo zäme isst, blibt zäme."</p>
                        <p className="text-xs uppercase tracking-widest">Quatiers-Pizza-Ofe Projekt &copy; 2024</p>
                    </footer>
                </div>
            );
        }

        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<App />);
    </script>
</body>
</html>
