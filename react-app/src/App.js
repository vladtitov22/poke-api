import React, { useState } from "react";
import "./App.css";

function App() {
  const [pokemon, setPokemon] = useState(""); 
  const [data, setData] = useState(null); 
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const fetchPokemon = async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(
        `http://php-backend/index.php?pokemon=${pokemon}`
      );
      if (!response.ok) {
        throw new Error("Pokémon nicht gefunden");
      }
      const result = await response.json();
      setData(result);
    } catch (err) {
      setError(err.message);
      setData(null);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="app-container">
      <h1 className="app-title">Pokémon-Suche</h1>
      <div className="search-container">
        <input
          type="text"
          className="search-input"
          placeholder="Geben Sie den Namen eines Pokémon ein"
          value={pokemon}
          onChange={(e) => setPokemon(e.target.value.toLowerCase())}
        />
        <button className="search-button" onClick={fetchPokemon}>
          Suchen
        </button>
      </div>
      {loading && <p className="loading-text">Laden...</p>}
      {error && <p className="error-text">{error}</p>}
      {data && (
        <div className="pokemon-container">
          <h2 className="pokemon-name">{data.name}</h2>
          <div className="images-container">
            {data.images.front && (
              <img
                src={data.images.front}
                alt="Vorderansicht"
                className="pokemon-image"
              />
            )}
            {data.images.back && (
              <img
                src={data.images.back}
                alt="Rückansicht"
                className="pokemon-image"
              />
            )}
            {data.images.shiny && (
              <img
                src={data.images.shiny}
                alt="Schillernde Version"
                className="pokemon-image"
              />
            )}
          </div>
          <div className="pokemon-description">
            <p>Gewicht: {data.weight}</p>
            <p>Höhe: {data.height}</p>
            <p>
              <strong>Fähigkeiten:</strong>{" "}
              {data.abilities && data.abilities.length > 0
                ? data.abilities.join(", ")
                : "Keine Fähigkeiten verfügbar"}
            </p>
          </div>
        </div>
      )}
    </div>
  );
}

export default App;
