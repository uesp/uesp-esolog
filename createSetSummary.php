<?php

$TABLE_SUFFIX = "49";
$LANG_SUFFIX = "";		//Use empty string for english
$SOURCEITEMTABLE = "Summary";
$KEEPONLYNEWSETS = false;
$REMOVEDUPLICATES = true;
$SHOW_ONLY_SET = "";
$QUIET = true;

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Updating item set data from mined item summaries for version $TABLE_SUFFIX, language '$LANG_SUFFIX'...\n");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$ESO_SET_INDEXES_FR = Array(
	1 => utf8_encode("Guilde Des Mages"),
	2 => utf8_encode("Guilde Des Guerriers"),
	3 => utf8_encode("Guilde Des Voleurs"),
	4 => utf8_encode("Indomptables"),
	6 => utf8_encode("Loups-Garous"),
	7 => utf8_encode("Magie Des Âmes"),
	8 => utf8_encode("Craglorn Test"),
	13 => utf8_encode("Confrérie Noire"),
	16 => utf8_encode("Ordre Psijique"),
	37 => utf8_encode("Sondage"),
	38 => utf8_encode("Excavation"),
	19 => utf8_encode("Vêtements Du Sorcier"),
	20 => utf8_encode("Armure D'homme-Médecine"),
	21 => utf8_encode("Garde Du Dragon Akaviroise"),
	22 => utf8_encode("Manteau Du Rêveur"),
	23 => utf8_encode("Esprit De L'archer"),
	24 => utf8_encode("Chance Du Valet"),
	25 => utf8_encode("Rose Du Désert"),
	26 => utf8_encode("Haillons De Prisonnier"),
	27 => utf8_encode("Héritage De Fiord"),
	28 => utf8_encode("Peau D'écorce"),
	29 => utf8_encode("Cotte De Mailles Du Sergent"),
	30 => utf8_encode("Carapace De Foudroptère"),
	31 => utf8_encode("Atours Du Soleil"),
	32 => utf8_encode("Froc Du Guérisseur"),
	33 => utf8_encode("Morsure De La Vipère"),
	34 => utf8_encode("Étreinte De La Mère De La Nuit"),
	35 => utf8_encode("Chevalier-Cauchemar"),
	36 => utf8_encode("Armure De L'Héritage Voilé"),
	37 => utf8_encode("Vent Mortel"),
	38 => utf8_encode("Étreinte Du Crépuscule"),
	39 => utf8_encode("Ordre D'Alessia"),
	40 => utf8_encode("Silence De La Nuit"),
	41 => utf8_encode("Rétribution De Blancserpent"),
	43 => utf8_encode("Armure De La Séductrice"),
	44 => utf8_encode("Baiser Du Vampire"),
	46 => utf8_encode("Atours Du Noble Duelliste"),
	47 => utf8_encode("Robes De La Main De Gloire"),
	48 => utf8_encode("Présent De Magnus"),
	49 => utf8_encode("Ombre Du Mont Écarlate"),
	50 => utf8_encode("Morag Tong"),
	51 => utf8_encode("Regard De La Mère De La Nuit"),
	52 => utf8_encode("Appel De L'acier"),
	53 => utf8_encode("Fourneau De Glace"),
	54 => utf8_encode("Poigne De Cendres"),
	55 => utf8_encode("Châle De Prière"),
	56 => utf8_encode("Étreinte De Stendarr"),
	57 => utf8_encode("Emprise De Syrabane"),
	58 => utf8_encode("Peau De Loup-Garou"),
	59 => utf8_encode("Baiser De Kyne"),
	60 => utf8_encode("Sentier Obscur"),
	61 => utf8_encode("Tueur Du Roi Dreugh"),
	62 => utf8_encode("Coquille Du Rejeton"),
	63 => utf8_encode("Mastodonte"),
	64 => utf8_encode("Parure Du Bateleur D'ombre"),
	65 => utf8_encode("Toucher De Sangrépine"),
	66 => utf8_encode("Robes De L'Hist"),
	67 => utf8_encode("Marcheuse D'ombres"),
	68 => utf8_encode("Stygien"),
	69 => utf8_encode("Parure De L'éclaireur"),
	70 => utf8_encode("Brute De La Septième Légion"),
	71 => utf8_encode("Fléau De Durok"),
	72 => utf8_encode("Armure Lourde De Nikulas"),
	73 => utf8_encode("Adversaire D'Oblivion"),
	74 => utf8_encode("Œil Du Spectre"),
	75 => utf8_encode("Pacte De Torug"),
	76 => utf8_encode("Robes De Maîtrise De Transformation"),
	77 => utf8_encode("Croisé"),
	78 => utf8_encode("Écorce D'Hist"),
	79 => utf8_encode("Sentier Des Saules"),
	80 => utf8_encode("Rage De Hunding"),
	81 => utf8_encode("Chant De Lamae"),
	82 => utf8_encode("Rempart D'Alessia"),
	83 => utf8_encode("Fléau Des Elfes"),
	84 => utf8_encode("Écailles D'Orgnum"),
	85 => utf8_encode("Clémence D'Almalexia"),
	86 => utf8_encode("Élégance De La Reine"),
	87 => utf8_encode("Yeux De Mara"),
	88 => utf8_encode("Robes De Maîtrise De La Destruction"),
	89 => utf8_encode("Sentinelle"),
	90 => utf8_encode("Morsure De Senche"),
	91 => utf8_encode("Tranchant D'Oblivion"),
	92 => utf8_encode("Espoir De Kagrenac"),
	93 => utf8_encode("Armure De Plate Du Chevalier-Tempête"),
	94 => utf8_encode("Armure Bénie De Méridia"),
	95 => utf8_encode("Malédiction De Shalidor"),
	96 => utf8_encode("Armure De Vérité"),
	97 => utf8_encode("Archimage"),
	98 => utf8_encode("Nécropotence"),
	99 => utf8_encode("Salut"),
	100 => utf8_encode("Œil De Faucon"),
	101 => utf8_encode("Affliction"),
	102 => utf8_encode("Écailles De L'éventreur Des Dunes"),
	103 => utf8_encode("Fourneau De Magie"),
	104 => utf8_encode("Mangeur De Malédiction"),
	105 => utf8_encode("Sœurs Jumelles"),
	106 => utf8_encode("Arche De La Reine-Nature"),
	107 => utf8_encode("Bénédiction Du Wyrd"),
	108 => utf8_encode("Ensemble Du Ravageur"),
	109 => utf8_encode("Lumière De Cyrodiil"),
	110 => utf8_encode("Sanctuaire"),
	111 => utf8_encode("Défense De Cyrodiil"),
	112 => utf8_encode("Terreur Nocturne"),
	113 => utf8_encode("Armoiries De Cyrodiil"),
	114 => utf8_encode("Âme Lumineuse"),
	116 => utf8_encode("Suite De Destruction"),
	117 => utf8_encode("Reliques Du Docteur Ansur"),
	118 => utf8_encode("Trésors De La Forgeterre"),
	119 => utf8_encode("Reliques De La Rébellion"),
	120 => utf8_encode("Armes D'Infernace"),
	121 => utf8_encode("Armes Des Ancêtres"),
	122 => utf8_encode("Armure D'ébène"),
	123 => utf8_encode("Meute D'Hircine"),
	124 => utf8_encode("Tenue Du Ver"),
	125 => utf8_encode("Fureur De L'Empire"),
	126 => utf8_encode("Grâce Des Anciens"),
	127 => utf8_encode("Frappe Mortelle"),
	128 => utf8_encode("Bénédiction Des Monarques"),
	129 => utf8_encode("Rétribution"),
	130 => utf8_encode("Œil D'aigle"),
	131 => utf8_encode("Bastion Du Continent"),
	132 => utf8_encode("Bouclier Du Vaillant"),
	133 => utf8_encode("Boutoir De Rapidité"),
	134 => utf8_encode("Suaire De La Liche"),
	135 => utf8_encode("Héritage Du Draugr"),
	136 => utf8_encode("Guerrier Immortel"),
	137 => utf8_encode("Guerrier Furieux"),
	138 => utf8_encode("Guerrier Défenseur"),
	139 => utf8_encode("Mage Avisé"),
	140 => utf8_encode("Mage Destructeur"),
	141 => utf8_encode("Mage Guérisseur"),
	142 => utf8_encode("Serpent Rapide"),
	143 => utf8_encode("Serpent Venimeux"),
	144 => utf8_encode("Serpent À Deux Crocs"),
	145 => utf8_encode("Voie Du Feu"),
	146 => utf8_encode("Voie De L'air"),
	147 => utf8_encode("Voie De La Connaissance Martiale"),
	148 => utf8_encode("Voie De L'arène"),
	155 => utf8_encode("Bastion Indomptable"),
	156 => utf8_encode("Infiltrateur Indomptable"),
	157 => utf8_encode("Détrameur Indomptable"),
	158 => utf8_encode("Bouclier De Braise"),
	159 => utf8_encode("Scindeflamme"),
	160 => utf8_encode("Tramesort Ardent"),
	161 => utf8_encode("Étoile Gémellaire"),
	162 => utf8_encode("Engeance De Méphala"),
	163 => utf8_encode("Engeance De Sang"),
	164 => utf8_encode("Seigneur Gardien"),
	165 => utf8_encode("Moissonneur Calamiteux"),
	166 => utf8_encode("Gardien Du Moteur"),
	167 => utf8_encode("Nocteflamme"),
	168 => utf8_encode("Nérien'eth"),
	169 => utf8_encode("Valkyn Skoria"),
	170 => utf8_encode("Gueule De L'Infernal"),
	171 => utf8_encode("Éternel Guerrier"),
	172 => utf8_encode("Infaillible Mage"),
	173 => utf8_encode("Cruel Serpent"),
	176 => utf8_encode("Butin Du Noble"),
	177 => utf8_encode("Redistributeur"),
	178 => utf8_encode("Maître Armurier"),
	179 => utf8_encode("Rose Noire"),
	180 => utf8_encode("Assaut Puissant"),
	181 => utf8_encode("Service Émérite"),
	183 => utf8_encode("Molag Kena"),
	184 => utf8_encode("Marques D'Imperium"),
	185 => utf8_encode("Puissance Curative"),
	186 => utf8_encode("Armes De La Décharge"),
	187 => utf8_encode("Pilleur Du Marais"),
	188 => utf8_encode("Maîtrise De La Tempête"),
	190 => utf8_encode("Mage Brûlant"),
	193 => utf8_encode("Élan De Suprématie"),
	194 => utf8_encode("Médecin De Terrain"),
	195 => utf8_encode("Venin Absolu"),
	196 => utf8_encode("Plaque Sangsue"),
	197 => utf8_encode("Tortionnaire"),
	198 => utf8_encode("Voleur D'essence"),
	199 => utf8_encode("Brise-Bouclier"),
	200 => utf8_encode("Phénix"),
	201 => utf8_encode("Armure Réactive"),
	204 => utf8_encode("Endurance"),
	205 => utf8_encode("Volonté"),
	206 => utf8_encode("Agilité"),
	207 => utf8_encode("Loi De Julianos"),
	208 => utf8_encode("Épreuve Du Feu"),
	209 => utf8_encode("Armure Du Code"),
	210 => utf8_encode("Marque Du Paria"),
	211 => utf8_encode("Permafrost"),
	212 => utf8_encode("Roncecœur"),
	213 => utf8_encode("Défense Glorieuse"),
	214 => utf8_encode("Para Bellum"),
	215 => utf8_encode("Succession Élémentaire"),
	216 => utf8_encode("Chef De La Chasse"),
	217 => utf8_encode("Nédhiver"),
	218 => utf8_encode("Valeur De Trinimac"),
	219 => utf8_encode("Morkuldin"),
	224 => utf8_encode("Faveur De Tava"),
	225 => utf8_encode("Alchimiste Astucieux"),
	226 => utf8_encode("Chasse Éternelle"),
	227 => utf8_encode("Malédiction De Bahraha"),
	228 => utf8_encode("Écailles De Syvarra"),
	229 => utf8_encode("Remède Du Crépuscule"),
	230 => utf8_encode("Danselune"),
	231 => utf8_encode("Bastion Lunaire"),
	232 => utf8_encode("Rugissement D'Alkosh"),
	234 => utf8_encode("Emblème Du Tireur D'élite"),
	235 => utf8_encode("Robes De Transmutation"),
	236 => utf8_encode("Mort Cruelle"),
	237 => utf8_encode("Focalisation De Léki"),
	238 => utf8_encode("Perfidie De Fasalla"),
	239 => utf8_encode("Furie Du Guerrier"),
	240 => utf8_encode("Gladiateur De Kvatch"),
	241 => utf8_encode("Héritage De Varen"),
	242 => utf8_encode("Courroux De Pélinal"),
	243 => utf8_encode("Peau De Morihaus"),
	244 => utf8_encode("Stratège Du Débordement"),
	245 => utf8_encode("Caresse De Sithis"),
	246 => utf8_encode("Vengeance De Galérion"),
	247 => utf8_encode("Vice-Chanoine Du Venin"),
	248 => utf8_encode("Muscles Du Héraut"),
	253 => utf8_encode("Physique Impérial"),
	256 => utf8_encode("Gros Chudan"),
	257 => utf8_encode("Velidreth"),
	258 => utf8_encode("Plasme Ambré"),
	259 => utf8_encode("Châtiment D'Heem-Jas"),
	260 => utf8_encode("Aspect De Mazzatun"),
	261 => utf8_encode("Diaphane"),
	262 => utf8_encode("Deuil"),
	263 => utf8_encode("Main De Méphala"),
	264 => utf8_encode("Araignée Géante"),
	265 => utf8_encode("Taillombre"),
	266 => utf8_encode("Kra'gh"),
	267 => utf8_encode("Mère De La Nuée"),
	268 => utf8_encode("Sentinelle De Rkugamz"),
	269 => utf8_encode("Ronce Étouffeuse"),
	270 => utf8_encode("Rampefange"),
	271 => utf8_encode("Sellistrix"),
	272 => utf8_encode("Gardien Infernal"),
	273 => utf8_encode("Ilambris"),
	274 => utf8_encode("Cœur-De-Glace"),
	275 => utf8_encode("Poigne-Tempête"),
	276 => utf8_encode("Tremblécaille"),
	277 => utf8_encode("Pirate Squelettique"),
	278 => utf8_encode("Roi Des Trolls"),
	279 => utf8_encode("Sélène"),
	280 => utf8_encode("Grothdarr"),
	281 => utf8_encode("Armure Du Débutant"),
	282 => utf8_encode("Cape Du Vampire"),
	283 => utf8_encode("Chante-Épée"),
	284 => utf8_encode("Ordre De Diagna"),
	285 => utf8_encode("Seigneur Vampire"),
	286 => utf8_encode("Ronces Du Spriggan"),
	287 => utf8_encode("Pacte Vert"),
	288 => utf8_encode("Harnachement De L'apiculteur"),
	289 => utf8_encode("Tenue Du Trameur"),
	290 => utf8_encode("Traficant De Skouma"),
	291 => utf8_encode("Exosquelette De Shalk"),
	292 => utf8_encode("Chagrin Maternel"),
	293 => utf8_encode("Médecin De La Peste"),
	294 => utf8_encode("Héritage D'Ysgramor"),
	295 => utf8_encode("Évasion"),
	296 => utf8_encode("Spéléologue"),
	297 => utf8_encode("Capuchon De L'adepte De L'Araignée"),
	298 => utf8_encode("Orateur Lumineux"),
	299 => utf8_encode("Rangée De Dents"),
	300 => utf8_encode("Toucher Du Netch"),
	301 => utf8_encode("Force De L'automate"),
	302 => utf8_encode("Léviathan"),
	303 => utf8_encode("Chant De La Lamie"),
	304 => utf8_encode("Méduse"),
	305 => utf8_encode("Chasseur De Trésors"),
	307 => utf8_encode("Draugr Colossal"),
	308 => utf8_encode("Haillons Du Pirate Squelettique"),
	309 => utf8_encode("Maille De Chevalier Errant"),
	310 => utf8_encode("Danse Des Épées"),
	311 => utf8_encode("Provocateur"),
	313 => utf8_encode("Fendoir Tinanesque"),
	314 => utf8_encode("Remède Perforant"),
	315 => utf8_encode("Entailles Cuisantes"),
	316 => utf8_encode("Flèche Caustique"),
	317 => utf8_encode("Impact Destructeur"),
	318 => utf8_encode("Grand Rajeunissement"),
	320 => utf8_encode("Vierge Guerrière"),
	321 => utf8_encode("Profanateur"),
	322 => utf8_encode("Guerrier-Poète"),
	323 => utf8_encode("Duplicité De L'assassin"),
	324 => utf8_encode("Tromperie Daedrique"),
	325 => utf8_encode("Brise-Entraves"),
	326 => utf8_encode("Défi De L'avant-Garde"),
	327 => utf8_encode("Barda Du Couard"),
	328 => utf8_encode("Tueur De Chevalier"),
	329 => utf8_encode("Riposte Du Sorcier"),
	330 => utf8_encode("Défense Automatique"),
	331 => utf8_encode("Machine De Guerre"),
	332 => utf8_encode("Maître Architecte"),
	333 => utf8_encode("Garde De L'inventeur"),
	334 => utf8_encode("Armure Imprenable"),
	335 => utf8_encode("Repos Du Draugr"),
	336 => utf8_encode("Pilier De Nirn"),
	337 => utf8_encode("Sang De Fer"),
	338 => utf8_encode("Fleur De Feu"),
	339 => utf8_encode("Buveur De Sang"),
	340 => utf8_encode("Jardin De La Harfreuse"),
	341 => utf8_encode("Sangreterre"),
	342 => utf8_encode("Domihaus"),
	343 => utf8_encode("Héritage De Caluurion"),
	344 => utf8_encode("Apparence De Vivification"),
	345 => utf8_encode("Faveur D'Ulfnor"),
	346 => utf8_encode("Conseil De Jorvuld"),
	347 => utf8_encode("Lance-Peste"),
	348 => utf8_encode("Malédiction De Doylemish"),
	349 => utf8_encode("Thurvokun"),
	350 => utf8_encode("Zaan"),
	351 => utf8_encode("Axiome Inné"),
	352 => utf8_encode("Airain Fortifié"),
	353 => utf8_encode("Acuité Mécanique"),
	354 => utf8_encode("Bricoleur Fou"),
	355 => utf8_encode("Ténèbres Insondables"),
	356 => utf8_encode("Haute Tension"),
	357 => utf8_encode("Entaille Disciplinée Perfectionnée"),
	358 => utf8_encode("Position Défensive Perfectionnée"),
	359 => utf8_encode("Tourbillon Chaotique Perfectionné"),
	360 => utf8_encode("Jaillissement Perforant Perfectionné"),
	361 => utf8_encode("Force Concentrée Perfectionnée"),
	362 => utf8_encode("Bénédiction Intemporelle Perfectionnée"),
	363 => utf8_encode("Entaille Disciplinée"),
	364 => utf8_encode("Position Défensive"),
	365 => utf8_encode("Tourbillon Chaotique"),
	366 => utf8_encode("Jaillissement Perforant"),
	367 => utf8_encode("Force Concentrée"),
	368 => utf8_encode("Bénédiction Intemporelle"),
	369 => utf8_encode("Charge Impitoyable"),
	370 => utf8_encode("Entaille Ravageuse"),
	371 => utf8_encode("Déluge Cruel"),
	372 => utf8_encode("Volée Tonitruante"),
	373 => utf8_encode("Mur Écrasant"),
	374 => utf8_encode("Régénération Précise"),
	380 => utf8_encode("Ensemble Du Prophète"),
	381 => utf8_encode("Âme Brisée"),
	382 => utf8_encode("Gracieuse Mélancolie"),
	383 => utf8_encode("Férocité Du Griffon"),
	384 => utf8_encode("Sagesse De Vanus"),
	385 => utf8_encode("Adepte Cavalier"),
	386 => utf8_encode("Aspect Du Sload"),
	387 => utf8_encode("Faveur De Nocturne"),
	388 => utf8_encode("Égide De Galenwe"),
	389 => utf8_encode("Armes De Relequen"),
	390 => utf8_encode("Manteau De Siroria"),
	391 => utf8_encode("Vêture D'Olorimë"),
	392 => utf8_encode("Égide De Galenwe Perfectionnée"),
	393 => utf8_encode("Armes De Relequen Perfectionnées"),
	394 => utf8_encode("Manteau De Siroria Perfectionné"),
	395 => utf8_encode("Vêture D'Olorimë Perfectionnée"),
	397 => utf8_encode("Balorgh"),
	398 => utf8_encode("Vykosa"),
	399 => utf8_encode("Compassion De Hanu"),
	400 => utf8_encode("Lune De Sang"),
	401 => utf8_encode("Havre D'Ursus"),
	402 => utf8_encode("Chasseur Lunaire"),
	403 => utf8_encode("Loup-Garou Sauvage"),
	404 => utf8_encode("Ténacité Du Geôlier"),
	405 => utf8_encode("Vantardise De Vive-Gorge"),
	406 => utf8_encode("Duplicité D'Aiguemortes"),
	407 => utf8_encode("Champion De L'Hist"),
	408 => utf8_encode("Collectionneur De Marqueurs Funéraires"),
	409 => utf8_encode("Chaman Naga"),
	410 => utf8_encode("Puissance De La Légion Perdue"),
	411 => utf8_encode("Charge Vaillante"),
	412 => utf8_encode("Uppercut Radial"),
	413 => utf8_encode("Cape Spectrale"),
	414 => utf8_encode("Tir Virulent"),
	415 => utf8_encode("Impulsion Sauvage"),
	416 => utf8_encode("Garde Du Soigneur"),
	417 => utf8_encode("Fureur Indomptable"),
	418 => utf8_encode("Stratège Des Sorts"),
	419 => utf8_encode("Acrobate Du Champ De Bataille"),
	420 => utf8_encode("Soldat De L'angoisse"),
	421 => utf8_encode("Héros Inébranlable"),
	422 => utf8_encode("Défense Du Bataillon"),
	423 => utf8_encode("Charge Galante Perfectionnée"),
	424 => utf8_encode("Uppercut Radial Perfectionné"),
	425 => utf8_encode("Cape Spectrale Perfectionnée"),
	426 => utf8_encode("Tir Virulent Perfectionné"),
	427 => utf8_encode("Impulsion Sauvage Perfectionnée"),
	428 => utf8_encode("Garde Du Guérisseur Perfectionnée"),
	429 => utf8_encode("Puissant Glacier"),
	430 => utf8_encode("Bande De Guerre De Tzogvin"),
	431 => utf8_encode("Invocateur Glacial"),
	432 => utf8_encode("Gardien Des Pierres"),
	433 => utf8_encode("Observateur Glacial"),
	434 => utf8_encode("Trépas Des Récupérateurs"),
	435 => utf8_encode("Tonnerre Aurorien"),
	436 => utf8_encode("Symphonie De Lames"),
	437 => utf8_encode("Élu De Havreglace"),
	438 => utf8_encode("Détermination Du Senche-Raht"),
	439 => utf8_encode("Tutelle De Vastarië"),
	440 => utf8_encode("Alfiq Rusé"),
	441 => utf8_encode("Vêtement De Darloc Brae"),
	442 => utf8_encode("Appel Du Croque-Mort"),
	443 => utf8_encode("Œil De Nahviintaas"),
	444 => utf8_encode("Dévot Du Faux Dieu"),
	445 => utf8_encode("Dent De Lokkestiiz"),
	446 => utf8_encode("Griffe De Yolnahkriin"),
	448 => utf8_encode("Œil De Nahviintaas Perfectionné"),
	449 => utf8_encode("Dévot Du Faux Dieu Perfectionné"),
	450 => utf8_encode("Dent De Lokkestiiz Perfectionnée"),
	451 => utf8_encode("Griffe De Lokkestiiz Perfectionnée"),
	452 => utf8_encode("Soif Du Croc Creux"),
	453 => utf8_encode("Griffes De Dro'Zakar"),
	454 => utf8_encode("Résolution De Ranald"),
	455 => utf8_encode("Redressement De Z'en"),
	456 => utf8_encode("Moissonneur De Pestazur"),
	457 => utf8_encode("Profanation Du Dragon"),
	458 => utf8_encode("Grundwulf"),
	459 => utf8_encode("Maarselok"),
	465 => utf8_encode("Défenseur De Senchal"),
	466 => utf8_encode("Hâte Du Maraudeur"),
	467 => utf8_encode("Élite De La Garde Du Dragon"),
	468 => utf8_encode("Corsaire Intrépide"),
	469 => utf8_encode("Garde Du Dragon Antique"),
	470 => utf8_encode("Acolyte De La Nouvelle Lune"),
	471 => utf8_encode("Foyer De Hiti"),
	472 => utf8_encode("Force De Titan"),
	473 => utf8_encode("Tourment De Bani"),
	474 => utf8_encode("Poigne De Draugrien"),
	475 => utf8_encode("Mande-Égide"),
	476 => utf8_encode("Gardien Du Sépulcre"),
	478 => utf8_encode("Mère Ciannait"),
	479 => utf8_encode("Cauchemar De Kjalnar"),
	480 => utf8_encode("Riposte Critique"),
	481 => utf8_encode("Agresseur Déchaîné"),
	482 => utf8_encode("Combattant Indompté"),
	487 => utf8_encode("Répit De L'hiver"),
	488 => utf8_encode("Châtiment Venimeux"),
	489 => utf8_encode("Vigueur Éternelle"),
	490 => utf8_encode("Faveur De Stuhn"),
	491 => utf8_encode("Appétit Du Dragon"),
	492 => utf8_encode("Vent De Kyne"),
	493 => utf8_encode("Vent De Kyne Perfectionné"),
	494 => utf8_encode("Commandement De Vrol"),
	495 => utf8_encode("Commandement De Vrol Perfectionné"),
	496 => utf8_encode("Opportuniste Rugissant"),
	497 => utf8_encode("Opportuniste Rugissant Perfectionné"),
	498 => utf8_encode("Puissance De Yandir"),
	499 => utf8_encode("Puissance De Yandir Perfectionnée"),
	501 => utf8_encode("Étrangleurs Thrassiens"),
	503 => utf8_encode("Anneau De La Chasse Sauvage"),
	505 => utf8_encode("Torque De Constance Tonale"),
	506 => utf8_encode("Parasite Magique"),
	513 => utf8_encode("Traîtrise De Talfyg"),
	514 => utf8_encode("Terreur Déchaînée"),
	515 => utf8_encode("Crépuscule Écarlate"),
	516 => utf8_encode("Catalyseur Élémentaire"),
	517 => utf8_encode("Hurlement De Kraglen"),
	518 => utf8_encode("Génie D'Arkasis"),
	519 => utf8_encode("Arpenteurs De Neige"),
	520 => utf8_encode("Bande De Brutalité De Malacath"),
	521 => utf8_encode("Étreinte Du Seigneur De Sang"),
	522 => utf8_encode("Charge Impitoyable Perfectionnée"),
	523 => utf8_encode("Entaille Ravageuse Perfectionnée"),
	524 => utf8_encode("Déluge Cruel Perfectionné"),
	525 => utf8_encode("Volée Tonitruante Perfectionnée"),
	526 => utf8_encode("Mur Écrasant Perfectionné"),
	527 => utf8_encode("Régénération Précise Perfectionnée"),
	528 => utf8_encode("Fendoir Titanesque Perfectionné"),
	529 => utf8_encode("Remède Perforant Perfectionné"),
	530 => utf8_encode("Entailles Cuisantes Perfectionnées"),
	531 => utf8_encode("Flèche Caustique Perfectionnée"),
	532 => utf8_encode("Impact Destructeur Perfectionné"),
	533 => utf8_encode("Grand Rajeunissement Perfectionné"),
	534 => utf8_encode("Enveloppe De Pierre"),
	535 => utf8_encode("Dame Ronce"),
	536 => utf8_encode("Bastion Radieux"),
	537 => utf8_encode("Mande-Vide"),
	538 => utf8_encode("Défiance Du Chevalier-Sorcière"),
	539 => utf8_encode("Fureur De L'Aigle Rouge"),
	540 => utf8_encode("Héritage De Karth"),
	541 => utf8_encode("Ascension Aéthérienne"),
	542 => utf8_encode("Siphon De La Malédiction"),
	543 => utf8_encode("Hôte Pestilentiel"),
	544 => utf8_encode("Camouflet Explosif"),
	557 => utf8_encode("Lame Du Bourreau"),
	558 => utf8_encode("Percussion Du Vide"),
	559 => utf8_encode("Élan Frénétique"),
	560 => utf8_encode("Tir À Bout Portant"),
	561 => utf8_encode("Courroux Des Éléments"),
	562 => utf8_encode("Débordement De Force"),
	563 => utf8_encode("Lame Du Bourreau Perfectionnée"),
	564 => utf8_encode("Percussion Du Vide Perfectionnée"),
	565 => utf8_encode("Élan Frénétique Perfectionné"),
	566 => utf8_encode("Tir De Précision À Bout Portant Perfectionné"),
	567 => utf8_encode("Courroux Des Éléments Perfectionné"),
	568 => utf8_encode("Débordement De Force Perfectionné"),
	569 => utf8_encode("Furie Du Fidèle"),
	570 => utf8_encode("Colère De Kinras"),
	571 => utf8_encode("Ruée Du Dragon"),
	572 => utf8_encode("Ritualiste Déchaîné"),
	573 => utf8_encode("Domaine De Dagon"),
	574 => utf8_encode("Garde De Follicide"),
	575 => utf8_encode("Anneau De L'Ordre Pâle"),
	576 => utf8_encode("Perles Des Ehlnofey"),
	577 => utf8_encode("Béhémoth D'Encratis"),
	578 => utf8_encode("Baron Zaudrus"),
	579 => utf8_encode("Engelure"),
	580 => utf8_encode("Assassin Des Terres Mortes"),
	581 => utf8_encode("Pillard Du Marais"),
	582 => utf8_encode("Complice De L'Hist"),
	583 => utf8_encode("Conquérant De La Patrie"),
	584 => utf8_encode("Victoire Du Diamant"),
	585 => utf8_encode("Champion Des Saxhleel"),
	586 => utf8_encode("Tourment Des Sul-Xan"),
	587 => utf8_encode("Folie De Bahsei"),
	588 => utf8_encode("Serment Du Parlepierre"),
	589 => utf8_encode("Champion Des Saxhleel Perfectionné"),
	590 => utf8_encode("Tourment Des Sul-Xan Perfectionné"),
	591 => utf8_encode("Folie De Bahsei Perfectionnée"),
	592 => utf8_encode("Serment Du Parlepierre Perfectionné"),
	593 => utf8_encode("Regard De Sithis"),
	594 => utf8_encode("Kilt De Plongée De Harponneur"),
	596 => utf8_encode("Fête Du Porteur De Mort"),
	597 => utf8_encode("Chaîne Du Changeforme"),
	598 => utf8_encode("Zoal L'Éveillé"),
	599 => utf8_encode("Immolateur Charr"),
	600 => utf8_encode("Glorgoloch Le Destructeur"),
	602 => utf8_encode("Déchirure Du Serment Écarlate"),
	603 => utf8_encode("Festin De Scorion"),
	604 => utf8_encode("Élan D'agonie"),
	605 => utf8_encode("Veille De La Rose D'argent"),
	606 => utf8_encode("Mande-Tonnerre"),
	607 => utf8_encode("Gourmet Sinistre"),
	608 => utf8_encode("Prieur Thierric"),
	609 => utf8_encode("Magma Incarné"),
	610 => utf8_encode("Vitalité Perdue"),
	611 => utf8_encode("Démolisseur Des Terres Mortes"),
	612 => utf8_encode("Flasque De Fer"),
	613 => utf8_encode("Œil De L'Étreinte"),
	614 => utf8_encode("Garde D'Hexos"),
	615 => utf8_encode("Cruauté De La Kynmarcheuse"),
	616 => utf8_encode("Convergence Noire"),
	617 => utf8_encode("Brise-Peste"),
	618 => utf8_encode("Frisson De Hrothgar"),
	619 => utf8_encode("Maelström De Marigalig"),
	620 => utf8_encode("Représailles De Griffon"),
	621 => utf8_encode("Gardien Glacial"),
	622 => utf8_encode("Marée Renversée"),
	623 => utf8_encode("Vengeance Maudite Par L'orage"),
	624 => utf8_encode("Vigueur Du Spriggan"),
	625 => utf8_encode("Anneau De Majesté De Markyn"),
	626 => utf8_encode("Bande De Belharza"),
	627 => utf8_encode("Spallière De Ruine"),
	629 => utf8_encode("Cri De Ralliement"),
	630 => utf8_encode("Tranche Et Taille"),
	631 => utf8_encode("Aura Affaiblissante"),
	632 => utf8_encode("Kargaeda"),
	633 => utf8_encode("Nazaray"),
	634 => utf8_encode("Nunatak"),
	635 => utf8_encode("Dame Malygda"),
	636 => utf8_encode("Baron Thirsk"),
	640 => utf8_encode("Fureur De L'Ordre"),
	641 => utf8_encode("Dédain Du Serpent"),
	642 => utf8_encode("Tresse Druidique"),
	643 => utf8_encode("Bénédiction De L'Île-Haute"),
	644 => utf8_encode("Endurance Des Inébranlables"),
	645 => utf8_encode("Grimace Des Systres"),
	646 => utf8_encode("Tourbillon Des Profondeurs"),
	647 => utf8_encode("Lame De Corail"),
	648 => utf8_encode("Garde Perlescente"),
	649 => utf8_encode("Profit Du Pillard"),
	650 => utf8_encode("Profit Du Pillard Perfectionné"),
	651 => utf8_encode("Garde Perlescente Perfectionnée"),
	652 => utf8_encode("Lame De Corail Perfectionnée"),
	653 => utf8_encode("Tourbillon Des Profondeurs Perfectionné"),
	654 => utf8_encode("Murmures De Mora"),
	655 => utf8_encode("Solerets De Dov-Rha"),
	656 => utf8_encode("Ceinture De L'égide Du Gaucher"),
	657 => utf8_encode("Anneau De Serpent De Mer"),
	658 => utf8_encode("Anneau D'âme Du Chêne"),
	660 => utf8_encode("Zèle De Longuesouche"),
	661 => utf8_encode("Accord De Pierre"),
	662 => utf8_encode("Rage De L'Ursauk"),
	663 => utf8_encode("Couveuse Pangrit"),
	664 => utf8_encode("Inévitabilité Funèbre"),
	665 => utf8_encode("Emprise Du Phylactère"),
	666 => utf8_encode("Archidruide Devyric"),
	667 => utf8_encode("Portier Euphotique"),
	668 => utf8_encode("Langueur De Péryite"),
	669 => utf8_encode("Stratagème De Nocturne"),
	670 => utf8_encode("Baume De Mara"),
	671 => utf8_encode("Gourmet Des Ruelles"),
	672 => utf8_encode("Papillon Phénix Théurge"),
	673 => utf8_encode("Bastion Du Draoife"),
	674 => utf8_encode("Barde De Farce De Faune"),
	675 => utf8_encode("Gambade De La Tisseuse De Foudre"),
	676 => utf8_encode("Garde De Syrabane"),
	677 => utf8_encode("Rebuffade De La Chimère"),
	678 => utf8_encode("Brasseur De La Vieille Croissance"),
	679 => utf8_encode("Griffe Du Spectre Forestier"),
	680 => utf8_encode("Lien Du Maître Des Rites"),
	681 => utf8_encode("Hurlement Du Chien Nix"),
	682 => utf8_encode("Défenseur Telvanni"),
	683 => utf8_encode("Roksa Le Déformé"),
	684 => utf8_encode("Tailleur De Rune Éclatant"),
	685 => utf8_encode("Inspiration Apocryphale"),
	686 => utf8_encode("Abîmes"),
	687 => utf8_encode("Ozezan L'Infernal"),
	688 => utf8_encode("Serpent Dans Les Étoiles"),
	689 => utf8_encode("Brise-Carapace"),
	690 => utf8_encode("Jugement D'Akatosh"),
	691 => utf8_encode("Vêtements Du Chanoine De La Crypte"),
	692 => utf8_encode("Grèves D'environnement Ésotérique"),
	693 => utf8_encode("Torque Du Dernier Roi Ayléide"),
	694 => utf8_encode("Amulette D'ur-Mage Vélothi"),
	695 => utf8_encode("Destin Brisé"),
	696 => utf8_encode("Efficacité Telvanni"),
	697 => utf8_encode("Synthèse Du Chercheur"),
	698 => utf8_encode("Dualité De Vivec"),
	699 => utf8_encode("Camonna Tong"),
	700 => utf8_encode("Rôdeur Adamant"),
	701 => utf8_encode("Paix Et Sérénité"),
	702 => utf8_encode("Tourment D'Ansuul"),
	703 => utf8_encode("Épreuve De Détermination"),
	704 => utf8_encode("Espoir Transformatif"),
	705 => utf8_encode("Espoir Transformatif Perfectionné"),
	706 => utf8_encode("Épreuve De Détermination Perfectionnée"),
	707 => utf8_encode("Tourment D'Ansuul Perfectionné"),
	708 => utf8_encode("Paix Et Sérénité Perfectionnées"),
	711 => utf8_encode("Général Des Hautes Terres Coloviennes"),
	712 => utf8_encode("Chef De Guerre Des Monts Jerall"),
	713 => utf8_encode("Haut Commissaire De La Baie Nibenaise"),
	722 => utf8_encode("Hiérophante Réveillé"),
	723 => utf8_encode("Guerrier Aux Veines De Basalte"),
	724 => utf8_encode("Corruption Noble"),
	726 => utf8_encode("Tranche-Âme"),
	727 => utf8_encode("Monolithe Des Tempêtes"),
	728 => utf8_encode("Colère Du Soleil"),
	729 => utf8_encode("Jardinier Des Saisons"),
	730 => utf8_encode("Cendres D'Anthelmir"),
	731 => utf8_encode("Faim De Sluthrug"),
	732 => utf8_encode("Ancrage Du Gant Noir"),
	734 => utf8_encode("Assemblage D'Anthelmir"),
	735 => utf8_encode("Induction Du Sentier Aveugle"),
	736 => utf8_encode("Cauchemar Terni"),
	737 => utf8_encode("Fureur Réfléchie"),
	738 => utf8_encode("Aveugle"),
	754 => utf8_encode("Revanche Du Père-Chêne"),
	755 => utf8_encode("Lames Émoussées"),
	756 => utf8_encode("Bénédiction De Baan Dar"),
	757 => utf8_encode("Symétrie Du Weald"),
	758 => utf8_encode("Cru Macabre"),
	759 => utf8_encode("Refuge Ayléide"),
	760 => utf8_encode("Gardes À Vapeur De Rourken"),
	761 => utf8_encode("Capuche De La Reine Des Ombres"),
	762 => utf8_encode("Sainte Et La Séductrice"),
	763 => utf8_encode("Frappe Du Tharriker"),
	764 => utf8_encode("Sentinelle Des Hautes Terres"),
	765 => utf8_encode("Fils De La Guerre"),
	766 => utf8_encode("Thèse De Scribe De Mora"),
	767 => utf8_encode("Fragments Du Null Arca"),
	768 => utf8_encode("Échos Lumineux"),
	769 => utf8_encode("Chef-D'œuvre De Xoryn"),
	770 => utf8_encode("Chef-D'œuvre De Xoryn Perfectionné"),
	771 => utf8_encode("Échos Lumineux Perfectionnés"),
	772 => utf8_encode("Fragments Perfectionnés Du Null Arca"),
	773 => utf8_encode("Thèse De Scribe De Mora Perfectionnée"),
	775 => utf8_encode("Disjonction Giclante"),
	776 => utf8_encode("Brûlot"),
	777 => utf8_encode("Brise-Cadavre"),
	778 => utf8_encode("Fil Ombral"),
	779 => utf8_encode("Fanal D'Oblivion"),
	780 => utf8_encode("Lancier Aéthérique"),
	781 => utf8_encode("Cri De L'Aire"),
	782 => utf8_encode("Fouet De Traqueur"),
	783 => utf8_encode("Douleur Partagée"),
	784 => utf8_encode("Focalisation De Maître De Siège"),
	791 => utf8_encode("Désolation Du Rempart"),
	792 => utf8_encode("Arpents"),
	793 => utf8_encode("Huile De Netch"),
	794 => utf8_encode("Résonance De Vandorallen"),
	795 => utf8_encode("Tempête De Lames De Jerensi"),
	796 => utf8_encode("Bouclier De Vent De Lucilla"),
	797 => utf8_encode("Grain Punitif"),
	798 => utf8_encode("Unité Héroïque"),
	799 => utf8_encode("Nid De Jeune Griffon"),
	800 => utf8_encode("Rocher Nocif"),
	801 => utf8_encode("Orphéon Le Tacticien"),
	802 => utf8_encode("Charité D'Arkay"),
	803 => utf8_encode("Art De Chevalier De La Lampe"),
	804 => utf8_encode("Vol De Noireplume"),
	805 => utf8_encode("Source Des Trois Reines"),
	806 => utf8_encode("Dansemort"),
	807 => utf8_encode("Barricade De La Panse Garnie"),
	808 => utf8_encode("Fardeau Partagé"),
	809 => utf8_encode("Traquepiste Enfant-Des-Marées"),
	810 => utf8_encode("Camaraderie Fortifiante"),
	811 => utf8_encode("Chaussures De Danse Du Dieu Fou"),
	812 => utf8_encode("Mantelet Du Vide De Rakkhat"),
	813 => utf8_encode("Monomythe Reforgé"),
	814 => utf8_encode("Harmonie Du Chaos"),
	815 => utf8_encode("Sceau Cruel De Kazpian"),
	816 => utf8_encode("Arène De Douleur"),
	817 => utf8_encode("Convergence Reposante"),
	818 => utf8_encode("Convergence Reposante Parfaite"),
	819 => utf8_encode("Arène De Douleur Parfaite"),
	820 => utf8_encode("Sceau Cruel De Kazpian Parfait"),
	821 => utf8_encode("Harmonie Du Chaos Parfaite"),
	822 => utf8_encode("Puits Des Âmes Riches"),
	823 => utf8_encode("Fureur D'âmes De Vykand"),
	824 => utf8_encode("Acier De La Fonderie Noire"),
	825 => utf8_encode("Tissesort Du Xanmeer"),
	826 => utf8_encode("Outils Du Piégeur"),
	827 => utf8_encode("Domination De L'enveloppe De Pierre"),
	828 => utf8_encode("Monstruosité De Pierre Noire"),
	829 => utf8_encode("Bar-Sakka"),
	830 => utf8_encode("Lacération Magique"),
	831 => utf8_encode("Coup De Grâce"),
	832 => utf8_encode("Ultime Impassible"),
	845 => utf8_encode("Masque De Guerre Du Chasseur"),
	846 => utf8_encode("Genèse Du Xanmeer"),
	847 => utf8_encode("Monster Prototype 3"),
	848 => utf8_encode("Chevalière Des Chemins Brisés"),
	849 => utf8_encode("Aiguillon Luisant"),
	850 => utf8_encode("Mille Yeux"),
	851 => utf8_encode("Vacarme"),
	852 => utf8_encode("Prototype Mythic Ring - U49 Mythic Proto 3"),
	853 => utf8_encode("Prototype Mythic Ring - U49 Mythic Proto 4"),
	855 => utf8_encode("Volesang"),
	856 => utf8_encode("Prototype Mythic Necklace - U49 Mythic Proto 1"),
);


$options = getopt("dv");
if ($options['d'] != null || $options['v'] != null) $QUIET = false;


function TransformBonusDesc($desc)
{
	$newDesc = preg_replace('/\|c[0-9a-fA-F]{6}([^|]+)\|r/', '$1', $desc);
	//$newDesc = preg_replace('/\n/', ' ', $newDesc);
	return $newDesc;
}


function GetItemArmorTypeText ($value)
{
	static $VALUES = array(
			-1 => "",
			0 => "",
			1 => "Light",
			2 => "Medium",
			3 => "Heavy",
	);
	
	$key = (int) $value;
	
	if (array_key_exists($key, $VALUES)) return $VALUES[$key];
	return "$key?";
}


function GetItemWeaponTypeText ($value)
{
	static $VALUES = array(
			-1 => "",
			0 => "",
			1 => "Axe",
			2 => "Mace",
			3 => "Sword",
			4 => "Greatsword",
			5 => "Battleaxe",
			6 => "Maul",
			7 => "Prop",
			8 => "Bow",
			9 => "Resto",
			10 => "Rune",
			11 => "Dagger",
			12 => "Flame",
			13 => "Frost",
			14 => "Shield",
			15 => "Lightning",
	);
	
	$key = (int) $value;
	
	if (array_key_exists($key, $VALUES)) return $VALUES[$key];
	return "$key?";
}


function GetItemEquipTypeText ($value)
{
	static $VALUES = array(
			-1 => "",
			0 => "",
			1 => "Head",
			2 => "Neck",
			3 => "Chest",
			4 => "Shoulder",
			5 => "OneHand",
			6 => "TwoHand",
			7 => "OffHand",
			8 => "Waist",
			9 => "Leg",
			10 => "Feet",
			11 => "Costume",
			12 => "Ring",
			13 => "Hand",
			14 => "MainHand",
	);

	$key = (int) $value;

	if (array_key_exists($key, $VALUES)) return $VALUES[$key];
	return "$key?";
}


function GetItemTypeText ($value)
{
	static $VALUES = array(
			-1 => "",
			11 => "additive",
			33 => "alchemy_base",
			2 => "armor",
			24 => "armor_booster",
			45 => "armor_trait",
			47 => "ava_repair",
			41 => "blacksmithing_booster",
			36 => "blacksmithing_material",
			35 => "blacksmithing_raw_material",
			43 => "clothier_booster",
			40 => "clothier_material",
			39 => "clothier_raw_material",
			34 => "collectible",
			18 => "container",
			13 => "costume",
			14 => "disguise",
			12 => "drink",
			32 => "enchanting_rune",
			25 => "enchantment_booster",
			28 => "flavoring",
			4 => "food",
			21 => "glyph_armor",
			26 => "glyph_jewelry",
			20 => "glyph_weapon",
			10 => "ingredient",
			22 => "lockpick",
			16 => "lure",
			0 => "none",
			3 => "plug",
			30 => "poison",
			7 => "potion",
			17 => "raw_material",
			31 => "reagent",
			29 => "recipe",
			8 => "scroll",
			6 => "siege",
			19 => "soul_gem",
			27 => "spice",
			44 => "style_material",
			15 => "tabard",
			9 => "tool",
			48 => "trash",
			5 => "trophy",
			1 => "weapon",
			23 => "weapon_booster",
			46 => "weapon_trait",
			42 => "woodworking_booster",
			38 => "woodworking_material",
			37 => "woodworking_raw_material",
			49 => "spellcrafting_tablet",
			50 => "mount",
			51 => "potency_rune",
			52 => "aspect_rune",
			53 => "essence_rune",
	);
	
	$key = (int) $value;
	
	if (array_key_exists($key, $VALUES)) return $VALUES[$key];
	return "$key?";
}


function JoinArrayKeys ($array)
{
	$output = "";
	
	foreach($array as $key => $value)
	{
		if ($output != "") $output .= " ";
		$output = $output . $key;
	}
	
	return $output;
}


function CreateItemSlotString ($setSlots)
{
	$output = "";
	
	foreach($setSlots as $key => $value)
	{
		if ($output != "") $output .= " ";
		
		if ($key == "Heavy" || $key == "Medium" || $key == "Light")
		{
			if (count($value) >= 7)
				$output = $output . $key . "(All)";
			else
				$output = $output . $key . "(" . JoinArrayKeys($value) . ")";
		}
		elseif ($key == "Weapons")
		{
			if (count($value) >= 12)
				$output = $output . $key . "(All)";
			else
				$output = $output . $key . "(" . JoinArrayKeys($value) . ")";
		}
		else
		{
			$output = $output . $key;
		}
	}
	
	return $output;
}


function UpdateItemSlotArray (&$outputArray, $item)
{
	$itemName = $item['name'];
	
	$type = $item['type'];
	$weaponType = $item['weaponType'];
	$armorType = $item['armorType'];
	$equipType = $item['equipType'];
	$typeText = GetItemTypeText($type);
	$armorTypeText = GetItemArmorTypeText($armorType);
	$equipTypeText = GetItemEquipTypeText($equipType);
	$weaponTypeText = GetItemWeaponTypeText($weaponType);
	
	$output = &$outputArray;
	
	if ($armorTypeText != "")
	{
		if (!array_key_exists($armorTypeText, $outputArray)) $outputArray[$armorTypeText] = array();
		$output = &$outputArray[$armorTypeText];
		
		if ($equipTypeText != "")
		{
			$output[$equipTypeText] = 1;
		}
	}
	else if ($weaponTypeText != "")
	{
		if ($weaponTypeText == "Shield")
		{
			$output["Shield"] = 1;
		}
		else
		{
			if (!array_key_exists("Weapons", $outputArray)) $outputArray["Weapons"] = array();
			$output = &$outputArray["Weapons"];
			$output[$weaponTypeText] = 1;
		}
	}
	elseif ($equipTypeText != "") 
	{
		$output[$equipTypeText] = 1;
	}
	elseif ($typeText != "")
	{
		$output[$typeText] = 1;
	}
	
}

$ESO_SETINDEX_MAP = array();
$SET_INDEXES = &$ESO_SET_INDEXES;
if ($LANG_SUFFIX == "fr") $SET_INDEXES = &$ESO_SET_INDEXES_FR; 

foreach ($ESO_SET_INDEXES as $setIndex => $setName)
{
	$setName = strtolower($setName);
	if ($ESO_SETINDEX_MAP[$setName] != null) print ("\tWarning: Duplicate set index $setIndex for '$setName'!\n");
	$ESO_SETINDEX_MAP[$setName] = $setIndex;
}

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$db->query("SET NAMES utf8;");
$db->query("SET CHARACTER SET utf8;");

$query = "DROP TABLE IF EXISTS setSummaryTmp;";
$result = $db->query($query);
if (!$result) print("Error: Failed to delete table setSummaryTmp!\n{$db->error}");

$query = "CREATE TABLE IF NOT EXISTS setSummaryTmp(
			id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			setName TINYTEXT NOT NULL DEFAULT '',
			indexName TINYTEXT NOT NULL DEFAULT '',
			setMaxEquipCount TINYINT NOT NULL DEFAULT 0,
			setBonusCount TINYINT NOT NULL DEFAULT 0,
			itemCount INTEGER NOT NULL DEFAULT 0,
			setBonusDesc1 TEXT NOT NULL DEFAULT '',
			setBonusDesc2 TEXT NOT NULL DEFAULT '',
			setBonusDesc3 TEXT NOT NULL DEFAULT '',
			setBonusDesc4 TEXT NOT NULL DEFAULT '',
			setBonusDesc5 TEXT NOT NULL DEFAULT '',
			setBonusDesc6 TEXT NOT NULL DEFAULT '',
			setBonusDesc7 TEXT NOT NULL DEFAULT '',
			setBonusDesc8 TEXT NOT NULL DEFAULT '',
			setBonusDesc9 TEXT NOT NULL DEFAULT '',
			setBonusDesc10 TEXT NOT NULL DEFAULT '',
			setBonusDesc11 TEXT NOT NULL DEFAULT '',
			setBonusDesc12 TEXT NOT NULL DEFAULT '',
			setBonusDesc TEXT NOT NULL DEFAULT '',
			itemSlots TEXT NOT NULL DEFAULT '',
			gameId INTEGER NOT NULL DEFAULT 0,
			type TINYTEXT NOT NULL DEFAULT '',
			category TINYTEXT NOT NULL DEFAULT '',
			sources TINYTEXT NOT NULL DEFAULT '',
			FULLTEXT(setName, setBonusDesc1, setBonusDesc2, setBonusDesc3, setBonusDesc4, setBonusDesc5, setBonusDesc6, setBonusDesc7, setBonusDesc8, setBonusDesc9, setBonusDesc10, setBonusDesc11, setBonusDesc12)
		) ENGINE=MYISAM;";

$result = $db->query($query);
if (!$result) exit("ERROR: Database query error creating table!\n" . $db->error);

$ESO_SETINDEX_MAP = array();

foreach ($ESO_SET_INDEXES as $setIndex => $setName)
{
	$setName = strtolower($setName);
	if ($ESO_SETINDEX_MAP[$setName] != null) print ("\tWarning: Duplicate set index $setIndex for '$setName'!\n");
	$ESO_SETINDEX_MAP[$setName] = $setIndex;
}

$count = count($ESO_SETINDEX_MAP);
print("Found $count set index records!\n");

$query = "SELECT * FROM setInfo;";
$rowResult = $db->query($query);
if (!$rowResult) exit("ERROR: Database query error (loading setInfo)!\n" . $db->error);
$rowResult->data_seek(0);

$setInfos = [];

while (($row = $rowResult->fetch_assoc()))
{
	$setName = strtolower($row['setName']);
	$setInfos[$setName] = $row;
}

$count = count($setInfos);
print("Loaded $count setInfo records!\n");

$table = "minedItem$SOURCEITEMTABLE$LANG_SUFFIX$TABLE_SUFFIX";
$query = "SELECT * FROM $table WHERE setName!='' ORDER BY itemId DESC;";
$rowResult = $db->query($query);
if (!$rowResult) exit("ERROR: Database query error loading set items from table '$table'!\n" . $db->error);
$rowResult->data_seek(0);

$itemCount = 0;
$updateCount = 0;
$newCount = 0;
$setItemSlots = array();

while (($row = $rowResult->fetch_assoc()))
{
	$QUIET_SET = $QUIET;
	
	$itemType = intval($row['type']);
	if ($itemType == 18) continue;	//Ignore containers?
	
	++$itemCount;
	$setName = $row['setName'];
	
	$indexName = strtolower($setName);
	$indexName = str_replace("'", "", $indexName);
	$indexName = str_replace(",", "", $indexName);
	$indexName = str_replace(" ", "-", $indexName);
	
	$setBonusDesc1 = TransformBonusDesc($row['setBonusDesc1']);
	$setBonusDesc2 = TransformBonusDesc($row['setBonusDesc2']);
	$setBonusDesc3 = TransformBonusDesc($row['setBonusDesc3']);
	$setBonusDesc4 = TransformBonusDesc($row['setBonusDesc4']);
	$setBonusDesc5 = TransformBonusDesc($row['setBonusDesc5']);
	$setBonusDesc6 = TransformBonusDesc($row['setBonusDesc6']);
	$setBonusDesc7 = TransformBonusDesc($row['setBonusDesc7']);
	$setBonusDesc8 = TransformBonusDesc($row['setBonusDesc8']);
	$setBonusDesc9 = TransformBonusDesc($row['setBonusDesc9']);
	$setBonusDesc10 = TransformBonusDesc($row['setBonusDesc10']);
	$setBonusDesc11 = TransformBonusDesc($row['setBonusDesc11']);
	$setBonusDesc12 = TransformBonusDesc($row['setBonusDesc12']);
	$setBonusCount = 0;
	$setMaxEquipCount = $row['setMaxEquipCount'];
	if ($setMaxEquipCount == null || $setMaxEquipCount == "") $setMaxEquipCount = 1; 
	
	$lastBonusDesc = $setBonusDesc12;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc11;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc10;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc9;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc8;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc7;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc6;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc5;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc4;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc3;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc2;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc1;
	
	if ($setBonusDesc1 != "") $setBonusCount = 1;
	if ($setBonusDesc2 != "") $setBonusCount = 2;
	if ($setBonusDesc3 != "") $setBonusCount = 3;
	if ($setBonusDesc4 != "") $setBonusCount = 4;
	if ($setBonusDesc5 != "") $setBonusCount = 5;
	if ($setBonusDesc6 != "") $setBonusCount = 6;
	if ($setBonusDesc7 != "") $setBonusCount = 7;
	if ($setBonusDesc8 != "") $setBonusCount = 8;
	if ($setBonusDesc9 != "") $setBonusCount = 9;
	if ($setBonusDesc10 != "") $setBonusCount = 10;
	if ($setBonusDesc11 != "") $setBonusCount = 11;
	if ($setBonusDesc12 != "") $setBonusCount = 12;
	
	if (!array_key_exists($setName, $setItemSlots)) $setItemSlots[$setName] = array();
	UpdateItemSlotArray($setItemSlots[$setName], $row);
	
	$matches = array();
	$regResult = preg_match('/\(([0-9]+) items\)/', $lastBonusDesc, $matches);
	if ($regResult) $setMaxEquipCount = $matches[1];
	
	if (!$QUIET)
	{
		if ($SHOW_ONLY_SET == "" || $SHOW_ONLY_SET == $setName)
		{
			print("\tUpdating set $setName with $setMaxEquipCount items...\n");
			$QUIET_SET = false;
		}
		else
		{
			$QUIET_SET = true;
		}
	}
	//print("\t\t$setBonusDesc1 == " . $row['setBonusDesc1'] . "\n");
	
	$query = "SELECT * FROM setSummaryTmp WHERE setName=\"$setName\";";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error finding set!\n" . $db->error);
	
	$createNewSet = true;
	$updateId = -1;
	
	while ( ($newRow = $result->fetch_assoc()) )
	{
		$matches = true;
		
		$newBonusDesc1 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc1']);
		$newBonusDesc2 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc2']);
		$newBonusDesc3 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc3']);
		$newBonusDesc4 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc4']);
		$newBonusDesc5 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc5']);
		$newBonusDesc6 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc6']);
		$newBonusDesc7 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc7']);
		$newBonusDesc8 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc8']);
		$newBonusDesc9 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc9']);
		$newBonusDesc10 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc10']);
		$newBonusDesc11 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc11']);
		$newBonusDesc12 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc12']);
		
		if ($newBonusDesc1 != $setBonusDesc1) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #1 doesn't match!\n"); }
		if ($newBonusDesc2 != $setBonusDesc2) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #2 doesn't match!\n"); }
		if ($newBonusDesc3 != $setBonusDesc3) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #3 doesn't match!\n"); }
		if ($newBonusDesc4 != $setBonusDesc4) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #4 doesn't match!\n"); }
		if ($newBonusDesc5 != $setBonusDesc5) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #5 doesn't match!\n"); }
		if ($newBonusDesc6 != $setBonusDesc6) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #6 doesn't match!\n"); }
		if ($newBonusDesc7 != $setBonusDesc7) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #7 doesn't match!\n"); }
		if ($newBonusDesc8 != $setBonusDesc8) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #8 doesn't match!\n"); }
		if ($newBonusDesc9 != $setBonusDesc9) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #9 doesn't match!\n"); }
		if ($newBonusDesc10 != $setBonusDesc10) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #10 doesn't match!\n"); }
		if ($newBonusDesc11 != $setBonusDesc11) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #11 doesn't match!\n"); }
		if ($newBonusDesc12 != $setBonusDesc12) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #12 doesn't match!\n"); }
		if ($newRow['setMaxEquipCount'] != $setMaxEquipCount) { $matches = false; if (!$QUIET_SET) print("\t\tSet max equip count doesn't match!\n"); }
		
		if ($matches) 
		{
			$updateId = $newRow['id'];
			$createNewSet = false;
			break;
		}
	}
	
	if ($createNewSet)
	{
		++$newCount;
		
		$setBonusDesc = "";
		if ($setBonusDesc1 != "") $setBonusDesc .= $setBonusDesc1;
		if ($setBonusDesc2 != "") $setBonusDesc .= "\n".$setBonusDesc2;
		if ($setBonusDesc3 != "") $setBonusDesc .= "\n".$setBonusDesc3;
		if ($setBonusDesc4 != "") $setBonusDesc .= "\n".$setBonusDesc4;
		if ($setBonusDesc5 != "") $setBonusDesc .= "\n".$setBonusDesc5;
		if ($setBonusDesc6 != "") $setBonusDesc .= "\n".$setBonusDesc6;
		if ($setBonusDesc7 != "") $setBonusDesc .= "\n".$setBonusDesc7;
		if ($setBonusDesc8 != "") $setBonusDesc .= "\n".$setBonusDesc8;
		if ($setBonusDesc9 != "") $setBonusDesc .= "\n".$setBonusDesc9;
		if ($setBonusDesc10 != "") $setBonusDesc .= "\n".$setBonusDesc10;
		if ($setBonusDesc11 != "") $setBonusDesc .= "\n".$setBonusDesc11;
		if ($setBonusDesc12 != "") $setBonusDesc .= "\n".$setBonusDesc12;
		
		if (!$QUIET_SET) print("\t\tCreating new set $setName\n$setBonusDesc...\n");
		
		$gameIndex = $ESO_SETINDEX_MAP[strtolower($setName)];
		if ($gameIndex == null) $gameIndex = -1;
		
		$setInfo = $setInfos[strtolower($setName)];
		$setType = "";
		$setSources = "";
		
		if ($setInfo)
		{
			$setType = $db->real_escape_string($setInfo['type']);
			$setSources = $db->real_escape_string($setInfo['sources']);
		}
		else
		{
			print("\t\tWARNING: No $setName found in set info!\n");
		}
		
		$setName = $db->real_escape_string($setName);
		$indexName = $db->real_escape_string($indexName);
		$setBonusDesc = $db->real_escape_string($setBonusDesc);
		$setBonusDesc1 = $db->real_escape_string($setBonusDesc1);
		$setBonusDesc2 = $db->real_escape_string($setBonusDesc2);
		$setBonusDesc3 = $db->real_escape_string($setBonusDesc3);
		$setBonusDesc4 = $db->real_escape_string($setBonusDesc4);
		$setBonusDesc5 = $db->real_escape_string($setBonusDesc5);
		$setBonusDesc6 = $db->real_escape_string($setBonusDesc6);
		$setBonusDesc7 = $db->real_escape_string($setBonusDesc7);
		$setBonusDesc8 = $db->real_escape_string($setBonusDesc8);
		$setBonusDesc9 = $db->real_escape_string($setBonusDesc9);
		$setBonusDesc10 = $db->real_escape_string($setBonusDesc10);
		$setBonusDesc11 = $db->real_escape_string($setBonusDesc11);
		$setBonusDesc12 = $db->real_escape_string($setBonusDesc12);
		
		$query  = "INSERT INTO setSummaryTmp(setName, indexName, setMaxEquipCount, setBonusCount, itemCount, setBonusDesc1, setBonusDesc2, setBonusDesc3, setBonusDesc4, setBonusDesc5, setBonusDesc6, setBonusDesc7, setBonusDesc8, setBonusDesc9, setBonusDesc10, setBonusDesc11, setBonusDesc12, setBonusDesc, gameId, type, sources) ";
		$query .= "VALUES('$setName', '$indexName', $setMaxEquipCount, $setBonusCount, 1, '$setBonusDesc1', '$setBonusDesc2', '$setBonusDesc3', '$setBonusDesc4', '$setBonusDesc5', '$setBonusDesc6', '$setBonusDesc7', '$setBonusDesc8', '$setBonusDesc9', '$setBonusDesc10', '$setBonusDesc11', '$setBonusDesc12', '$setBonusDesc', $gameIndex, '$setType', '$setSources');";
		
		$result = $db->query($query);
		if (!$result) exit("ERROR: Database query error inserting into table!\n" . $db->error . "\n" . $query);
	}
	else if ($updateId > 0)
	{
		//if (!$QUIET_SET) print("\t\tUpdating set $updateId...\n");
		++$updateCount;
		$query = "UPDATE setSummaryTmp SET itemCount=itemCount+1 WHERE id=$updateId;";
		$result = $db->query($query);
		if (!$result) exit("ERROR: Database query error updating table!\n" . $db->error . "\n" . $query);
	}
	else
	{
		if (!$QUIET_SET) print("\t\tError: Unknown set record to update!\n");
	}
	
}

print("\tUpdating set item slots...\n");

foreach ($setItemSlots as $setName => $setSlots)
{
	$slotString = CreateItemSlotString($setSlots);
	$query = "UPDATE setSummaryTmp SET itemSlots='".$slotString."' WHERE setName=\"".$setName."\";";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error updating table!\n" . $db->error . "\n" . $query);
	//print("$setName: $slotString\n");
}

print("Found $itemCount item sets, $newCount new, $updateCount duplicate!\n");

if ($KEEPONLYNEWSETS && $TABLE_SUFFIX != "")
{
	print("\tDeleting existing sets in setSummary...\n");
	
	$query = "DELETE setSummaryTmp FROM setSummaryTmp LEFT JOIN setSummary on setSummaryTmp.setName = setSummary.setName WHERE setSummary.setName IS NOT NULL;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error deleting old sets!\n" . $db->error . "\n" . $query);
	
	print("\tDeleting old sets...OK!\n");
}

if ($REMOVEDUPLICATES)
{
	print("\tRemoving duplicates...\n");
	
	$query = "SELECT *, COUNT(*) c, GROUP_CONCAT(id) ids, GROUP_CONCAT(itemCount) itemCounts FROM setSummaryTmp GROUP BY setName HAVING c > 1;";
	$rowResult = $db->query($query);
	if (!$rowResult) exit("ERROR: Database query error finding duplicate sets!\n" . $db->error . "\n" . $query);
	
	while (($row = $rowResult->fetch_assoc()))
	{
		$setName = $row['setName'];
		$count = $row['c'];
		$id = $row['id'];
		$ids = explode(",", $row['ids']);
		$itemCounts = explode(",", $row['itemCounts']);
		
		print("\t\tFound duplicate set $setName ($c records, '{$row['ids']}', '{$row['itemCounts']}') \n");
		
		$maxCount = max($itemCounts);
		
		foreach ($itemCounts as $i => $itemCount)
		{
			$itemId = $ids[$i];
			if ($itemCount >= $maxCount) continue;
			
			print("\t\t\tDeleting record {$itemId} with count {$itemCount}...\n");
			
			$query = "DELETE FROM setSummaryTmp WHERE id=$itemId;";
			$deleteResult =	$db->query($query);
			if (!$deleteResult) exit("ERROR: Database query error deleting duplicate sets!\n" . $db->error . "\n" . $query);
		}
	}
}

$query = "DROP TABLE IF EXISTS setSummary$LANG_SUFFIX$TABLE_SUFFIX;";
$db->query($query);

$query = "RENAME TABLE setSummaryTmp TO setSummary$LANG_SUFFIX$TABLE_SUFFIX;";
$result = $db->query($query);
if ($result === false) exit("ERROR: Failed to rename temporary table to setSummary$LANG_SUFFIX$TABLE_SUFFIX!");


