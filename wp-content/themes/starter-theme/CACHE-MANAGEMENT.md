# Gestion du Cache - Guide de Dépannage

## 🚨 Problèmes de Cache Courants

Si vous rencontrez des erreurs 404 sur des fichiers qui existent ou des références à d'anciens chemins :

### Solution Rapide

```bash
npm run dev:clean
```

Cette commande nettoie automatiquement le cache Vite et relance le serveur.

## 📋 Commandes Disponibles

### Développement

```bash
npm run dev              # Démarre le serveur de développement
npm run dev:clean        # Nettoie le cache et démarre le serveur (recommandé après modifications structurelles)
```

### Build

```bash
npm run build            # Build de production
npm run build:clean      # Nettoie tout et fait un build propre
```

### Nettoyage

```bash
npm run clean:cache      # Supprime uniquement le cache Vite
npm run clean            # Supprime le cache ET le dossier dist
```

## 🔧 Quand Nettoyer le Cache ?

Nettoyez le cache dans ces situations :

- ✅ Après avoir renommé/déplacé des fichiers ou dossiers
- ✅ Après avoir modifié la structure du projet
- ✅ Après avoir changé les imports/exports de modules
- ✅ Si vous voyez des erreurs 404 sur des fichiers existants
- ✅ Si les modifications ne sont pas prises en compte malgré le HMR
- ✅ Après avoir changé de branche Git avec des modifications importantes

## 🔍 Nettoyage Complet (Cas Extrême)

Si les problèmes persistent après `npm run clean` :

```bash
# 1. Arrêter tous les serveurs Node
lsof -ti:5173 | xargs kill -9

# 2. Nettoyage complet
rm -rf node_modules/.vite dist node_modules/.cache

# 3. Réinstaller les dépendances (si nécessaire)
rm -rf node_modules package-lock.json
npm install

# 4. Redémarrer
npm run dev
```

## 💡 Bonnes Pratiques

1. **Utilisez `dev:clean` après des modifications structurelles** plutôt que `dev`
2. **Hard refresh dans le navigateur** : `Cmd+Shift+R` (Mac) ou `Ctrl+Shift+R` (Windows)
3. **Videz le cache du navigateur** si les assets CSS/JS ne se mettent pas à jour
4. **Redémarrez le serveur** après avoir ajouté de nouveaux fichiers dans `/animations`, `/classes`, etc.

## ⚙️ Configuration

Le fichier `vite.config.js` inclut maintenant :

- Une gestion optimisée du cache via `cacheDir`
- Une liste de dépendances pré-optimisées
- Un watch configuré pour ignorer les dossiers inutiles

Si vous continuez à avoir des problèmes de cache persistants, vous pouvez forcer la reconstruction en modifiant dans `vite.config.js` :

```javascript
optimizeDeps: {
  force: true, // ⚠️ Ralentit le démarrage mais garantit un cache propre
}
```

(Remettez à `false` une fois le problème résolu)
