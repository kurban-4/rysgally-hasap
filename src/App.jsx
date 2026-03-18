import { useState, useEffect } from "react";
import "./App.css";

function App() {
  const [products, setProducts] = useState([]);
  const [cart, setCart] = useState([]);

  // Загружаем товары при старте
  useEffect(() => {
    fetchProducts();
  }, []);

  const fetchProducts = async () => {
    try {
      const API_URL = import.meta.env.VITE_API_URL;
  fetch(`${API_URL}/products`)
      const data = await response.json();
      setProducts(data);
    } catch (e) { console.error("Server bagly däl"); }
  };

  const addToCart = (p) => setCart([...cart, p]);
  const total = cart.reduce((sum, item) => sum + item.price, 0);

  return (
    <div className="app-container">
      <aside className="sidebar">
        <h2>Rysgally Hasap</h2>
        <div className="stats">Jemi: {total} TMT</div>
        <button className="pay-button" onClick={() => alert('Töleg kabul edildi!')}>Töleg Et</button>
        <div className="cart-list">
          {cart.map((item, i) => (
            <div key={i} className="cart-item">{item.name} - {item.price}</div>
          ))}
        </div>
      </aside>
      
      <main className="product-grid">
        {products.map((p) => (
          <div key={p.id} className="product-card" onClick={() => addToCart(p)}>
            <h3>{p.name}</h3>
            <p>{p.price} TMT</p>
          </div>
        ))}
      </main>
    </div>
  );
}

export default App;