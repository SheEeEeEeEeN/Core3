const philippineLocations = {
    // ================= LUZON =================
    "NCR": {
        "Metro Manila": {
            "Manila": ["Binondo", "Ermita", "Intramuros", "Malate", "Paco", "Pandacan", "Port Area", "Quiapo", "Sampaloc", "San Andres", "San Nicolas", "Santa Ana", "Santa Cruz", "Santa Mesa", "Tondo I", "Tondo II"],
            "Quezon City": ["Bagumbayan", "Bahay Toro", "Batasan Hills", "Commonwealth", "Cubao", "Diliman", "Fairview", "Holy Spirit", "Kamuning", "Loyola Heights", "New Manila", "Novaliches", "Pasong Tamo", "Payatas", "Pinyahan", "Project 4", "Project 6", "San Bartolome", "Socorro", "Tandang Sora", "Ugong Norte", "West Triangle"],
            "Makati": ["Bangkal", "Bel-Air", "Cembo", "Comembo", "Dasma", "East Rembo", "Forbes Park", "Guadalupe Nuevo", "Guadalupe Viejo", "Magallanes", "Palanan", "Pembo", "Pinagkaisahan", "Pio del Pilar", "Poblacion", "Rizal", "San Antonio", "San Lorenzo", "Santa Cruz", "Tejeros", "Urdaneta", "West Rembo"],
            "Taguig": ["Bagong Tanyag", "Bambang", "Bonifacio Global City (BGC)", "Calzada", "Central Bicutan", "Fort Bonifacio", "Hagonoy", "Ibayo-Tipas", "Lower Bicutan", "Maharlika Village", "Pinagsama", "San Miguel", "Santa Ana", "Tuktukan", "Upper Bicutan", "Ususan", "Wawa", "Western Bicutan"],
            "Pasig": ["Bagong Ilog", "Bambang", "Buting", "Caniogan", "Dela Paz", "Kalawaan", "Kapasigan", "Kapitolyo", "Malinao", "Manggahan", "Maybunga", "Oranbo", "Palatiw", "Pinagbuhatan", "Pineda", "Rosario", "Sagad", "San Antonio", "San Joaquin", "San Miguel", "San Nicolas", "Santa Cruz", "Santa Lucia", "Sumilang", "Ugong"],
            "Caloocan": ["Bagong Barrio", "Bagong Silang", "Bagumbong", "Camarin", "Deparo", "Grace Park", "Kaybiga", "Llano", "Maypajo", "Monument", "Morning Breeze", "Sangandaan", "Tala"],
            "Parañaque": ["Baclaran", "BF Homes", "Don Bosco", "Don Galo", "La Huerta", "Marcelo Green", "Merville", "Moonwalk", "San Antonio", "San Dionisio", "San Isidro", "San Martin de Porres", "Santo Niño", "Sun Valley", "Tambo", "Vitalez"],
            "Pasay": ["Baclaran", "Barangay 1-201", "Malibay", "Maricaban", "Pio del Pilar", "San Jose", "San Rafael", "Santa Clara", "Santo Niño", "Villamor Airbase"]
        }
    },
    "Region III": {
        "Bulacan": {
            "Malolos": ["Poblacion", "Bulihan", "Dakila", "Liang", "Look 1st", "Mojon", "Sumapang Matanda", "Tikay"],
            "Meycauayan": ["Banca-Banca", "Caingin", "Camalig", "Hulo", "Iba", "Langka", "Lawang Bato", "Libtong", "Malhacan", "Perez", "Saluysoy"],
            "San Jose del Monte": ["Assumption", "Bagong Buhay", "Citrus", "Ciudad Real", "Dulong Bayan", "Fatima", "Francisco Homes", "Gaya-Gaya", "Graceville", "Gumaoc", "Kaypian", "Maharlika", "Muzon", "Paradise III", "Poblacion", "San Manuel", "San Martin", "San Pedro", "San Rafael", "San Roque", "Sapang Palay", "Santo Cristo", "Santo Niño", "Tungkong Mangga"]
        },
        "Pampanga": {
            "Angeles City": ["Anunas", "Balibago", "Capaya", "Claro M. Recto", "Cuayan", "Cutcut", "Lourdes", "Malabanias", "Margot", "Pandan", "Pulung Maragul", "Santo Rosario", "Sapangbato"],
            "San Fernando": ["Baliti", "Bulaon", "Calulut", "Dela Paz", "Del Pilar", "Dolores", "Juliana", "Lara", "Lourdes", "Magliman", "Maimpis", "Malpitic", "Pandaras", "Panipuan", "Pulung Bulu", "Quebiawan", "Saguin", "San Agustin", "San Isidro", "San Jose", "San Juan", "San Nicolas", "San Pedro", "Santa Lucia", "Santa Teresita", "Santo Niño", "Sindalan", "Telabastagan"]
        }
    },
    "Region IV-A": {
        "Cavite": {
            "Bacoor": ["Alima", "Aniban", "Bayanan", "Daang Bukid", "Digman", "Dulong Bayan", "Habay", "Ligas", "Mambog", "Molino I", "Molino II", "Molino III", "Molino IV", "Niog", "Panapaan", "Queens Row", "Real", "Salinas", "San Nicolas", "Sineguelasan", "Talaba", "Zapote"],
            "Dasmarinas": ["Burol", "Langkaan", "Paliparan", "Salawag", "Sampaloc", "San Agustin", "San Jose", "San Simon", "Santo Nino", "Zone I to IV"],
            "Imus": ["Alapan", "Anabu I", "Anabu II", "Bayan Luma", "Bucandala", "Carsadang Bago", "Malagasang", "Pag-Asa", "Palico", "Pasong Buaya", "Poblacion", "Tanzang Luma", "Toclong"]
        },
        "Laguna": {
            "Calamba": ["Bagong Kalsada", "Banlic", "Barandal", "Batino", "Bubuyan", "Bucal", "Bunggo", "Burol", "Camaligan", "Canlubang", "Halang", "Hornalan", "Laguerta", "La Mesa", "Lawa", "Lecheria", "Lingga", "Looc", "Mabato", "Majada Labas", "Makiling", "Mapagong", "Masili", "Maunong", "Mayapa", "Paciano Rizal", "Palingon", "Palo-Alto", "Pansol", "Parian", "Prinza", "Punta", "Puting Lupa", "Real", "Saimsim", "Sampiruhan", "San Cristobal", "San Jose", "San Juan", "Sirang Lupa", "Sucol", "Turbina", "Ulango", "Uwisan"],
            "Santa Rosa": ["Aplaya", "Balibago", "Caingin", "Dila", "Dita", "Don Jose", "Ibaba", "Kanluran", "Labas", "Macabling", "Malitlit", "Malusak", "Market Area", "Pooc", "Pulong Santa Cruz", "Santo Domingo", "Sinalhan", "Tagapo"]
        },
        "Rizal": {
            "Antipolo": ["Bagong Nayon", "Beverly Hills", "Calawis", "Cupang", "Dalig", "Dela Paz", "Inarawan", "Mambugan", "Mayamot", "Muntindilaw", "San Isidro", "San Jose", "San Juan", "San Luis", "San Roque", "Santa Cruz"],
            "Taytay": ["Dolores", "Muzon", "San Isidro", "San Juan", "Santa Ana"]
        }
    },

    // ================= VISAYAS =================
    "Region VI": {
        "Aklan": {
            "Kalibo": ["Andagao", "Bachaw Norte", "Bachaw Sur", "Brisban", "Buswang New", "Buswang Old", "Caano", "Estancia", "Linabuan Norte", "Molo", "Nalook", "Poblacion", "Pook", "Tigayon", "Tinigaw"],
            "Malay (Boracay)": ["Argao", "Balabag", "Balusbos", "Cabulihan", "Caticlan", "Cogon", "Cubay Norte", "Cubay Sur", "Dumlog", "Manoc-Manoc", "Motag", "Naasug", "Nabaoy", "Napaan", "Poblacion", "Sanabag", "Yapak"]
        },
        "Capiz": {
            "Roxas City": ["Adlawan", "Bago", "Balijuagan", "Banica", "Barra", "Bato", "Baybay", "Bolo", "Cabugao", "Cagay", "Cogon", "Culasi", "Dayao", "Dinginan", "Dumolog", "Gaban", "Inzo Arnaldo", "Jumaguicjic", "Lanot", "Lawaan", "Libas", "Loctugan", "Lonoy", "Milibili", "Mongpong", "Olotayan", "Punta Cogon", "Punta Tabuc", "San Jose", "Sibaguan", "Tanza", "Tiza"]
        },
        "Iloilo": {
            "Iloilo City": ["Arevalo", "City Proper", "Jaro", "La Paz", "Lapuz", "Mandurriao", "Molo"],
            "Oton": ["Poblacion", "San Nicolas", "Santa Rita", "Tagbac"],
            "Pavia": ["Aganan", "Amparo", "Anilao", "Balabag", "Cabugao", "Jibao-an", "Mali-ao", "Pagsanga-an", "Pandac", "Purok", "Tigum", "Ungka"]
        },
        "Negros Occidental": {
            "Bacolod City": ["Alijis", "Banago", "Barangay 1-41", "Bata", "Cabug", "Estefania", "Felisa", "Granada", "Handumanan", "Mandalagan", "Mansilingan", "Montevista", "Pahanocoy", "Punta Taytay", "Singcang-Airport", "Sum-ag", "Taculing", "Tangub", "Villamonte", "Vista Alegre"],
            "Silay City": ["Balaring", "Barangay I-V", "E. Lopez", "Guimbala-on", "Hawaiian", "Lantad", "Mambulac", "Rizal"]
        }
    },
    "Region VII": {
        "Bohol": {
            "Tagbilaran City": ["Bool", "Booy", "Cabawan", "Cogon", "Dao", "Dampas", "Manga", "Mansasa", "Poblacion I", "Poblacion II", "Poblacion III", "San Isidro", "Taloto", "Tiptip", "Ubujan"],
            "Panglao": ["Bil-isan", "Bolod", "Danao", "Doljo", "Libaong", "Looc", "Lourdes", "Poblacion", "Tangnan", "Tawala"]
        },
        "Cebu": {
            "Cebu City": ["Adlaon", "Apas", "Babag", "Banilad", "Basak Pardo", "Basak San Nicolas", "Bonbon", "Busay", "Calamba", "Camputhaw", "Capitol Site", "Carreta", "Cogon Ramos", "Day-as", "Ermita", "Guadalupe", "Hipodromo", "Inayawan", "Kalubihan", "Kamagayan", "Kasambagan", "Kinasang-an", "Labangon", "Lahug", "Lorega San Miguel", "Lusaran", "Luz", "Mabolo", "Mambaling", "Pahina Central", "Pari-an", "Pasil", "Pit-os", "Pung-ol Sibugay", "Punta Princesa", "Quiot", "Sambag I", "Sambag II", "San Antonio", "San Jose", "San Nicolas Proper", "Santa Cruz", "Santo Niño", "Sirao", "T. Padilla", "Talamban", "Taptap", "Tejero", "Tinago", "Tisa", "Toong", "Zapatera"],
            "Mandaue City": ["Alang-alang", "Bakilid", "Banilad", "Basak", "Cabancalan", "Cambaro", "Canduman", "Casuntingan", "Centro", "Cubacub", "Guizo", "Ibabao-Estancia", "Jagobiao", "Labogon", "Looc", "Maguikay", "Mantuyong", "Opao", "Paknaan", "Pagsabungan", "Subangdaku", "Tabok", "Tawason", "Tingub", "Tipolo", "Umapad"],
            "Lapu-Lapu City": ["Agus", "Babag", "Bankal", "Basak", "Buaya", "Calawisan", "Canjulao", "Gun-ob", "Ibo", "Looc", "Mactan", "Maribago", "Marigondon", "Pajac", "Pajo", "Pangan-an", "Poblacion", "Punta Engaño", "Pusok", "Subabasbas"]
        },
        "Negros Oriental": {
            "Dumaguete City": ["Bagacay", "Bajumpandan", "Balugo", "Banilad", "Bantayan", "Batinguel", "Bunao", "Cadawinonan", "Calindagan", "Camanjac", "Candau-ay", "Cantil-e", "Daro", "Junob", "Looc", "Mangnao", "Motong", "Piapi", "Poblacion 1-8", "Pulantubig", "Tabuc-tubig", "Taclobo", "Talay"]
        }
    },
    // --- ITO YUNG KULANG SA FILE MO KANINA ---
    "Region VIII": {
        "Leyte": {
            "Tacloban City": ["Abucay", "Bagacay", "Basper", "Cabalawan", "Caibaan", "Calanipawan", "Diit", "Fatima Village", "Marasbaras", "Naga-Naga", "Palanog", "Panalaron", "Poblacion (Barangay 1-110)", "Sagkahan", "San Jose", "Santo Niño", "Suhi", "Tagpuro", "V & G Subdivision"],
            "Ormoc City": ["Alegria", "Bagong Buhay", "Bantigue", "Batuan", "Bayog", "Camp Downes", "Can-adieng", "Cogon", "Curva", "Dayhagan", "District 1-29", "Ipil", "Linao", "Luna", "Mabini", "Macabug", "Naungan", "Punta", "Sabang", "San Isidro", "San Pablo", "Valencia"],
            "Palo": ["Arado", "Baras", "Buri", "Campetic", "Cogon", "Gacao", "Guindapunan", "Libertad", "Luntad", "Naga-naga", "Pawing", "Salvacion", "San Joaquin", "San Jose", "San Miguel", "Tacuranga"]
        },
        "Southern Leyte": {
            "Maasin City": ["Abgao", "Asuncion", "Bogo", "Combado", "Dongon", "Gapas-gapas", "Ibarra", "Isagani", "Laboon", "Lib-og", "Mambajao", "Mantahan", "Maria Clara", "Matin-ao", "Pasay", "Rizal", "San Rafael", "Santa Rosa", "Tagnipa", "Tunga-tunga"]
        },
        "Samar (Western Samar)": {
            "Catbalogan City": ["Bunuanan", "Canlapwas", "Guinsorongan", "Lagundi", "Maulong", "Mercedes", "Munoz", "Poblacion 1-13", "San Andres", "San Pablo", "Silanga", "Socorro"]
        },
        "Northern Samar": {
            "Catarman": ["Abad Santos", "Bangkerohan", "Baybay", "Cawayan", "Dalakit", "Ipil-ipil", "Jose Abad Santos", "Kawayan", "Narra", "Poblacion", "Sampaguita", "University Town", "Yakal"]
        }
    },

    // ================= MINDANAO =================
    "Region X": {
        "Misamis Oriental": {
            "Cagayan de Oro": ["Agusan", "Balulang", "Barangay 1-40", "Bayabas", "Bonbon", "BugXZo", "Bulua", "Camaman-an", "Canitoan", "Carmen", "Consolacion", "Cugman", "Gusa", "Indahag", "Iponan", "Kauswagan", "Lapasan", "Lumbia", "Macabalan", "Macasandig", "Nazareth", "Patag", "Puerto", "Puntod", "Tablon"]
        }
    },
    "Region XI": {
        "Davao del Sur": {
            "Davao City": ["Agdao", "Bago Aplaya", "Bago Gallera", "Bago Oshiro", "Baguio", "Balusong", "Biao Escuela", "Binugao", "Bucana", "Buhangin", "Bunawan", "Cabantian", "Cadalian", "Calinan", "Catalunan Grande", "Catalunan Pequeño", "Crossing Bayabas", "Dacudao", "Dumoy", "Eden", "Gumalang", "Ilang", "Indangan", "Langub", "Lapu-Lapu", "Los Amigos", "Lubogan", "Ma-a", "Mabuhay", "Magtuod", "Mahayag", "Malabog", "Malagos", "Mandug", "Matina Aplaya", "Matina Crossing", "Matina Pangi", "Mintal", "Mudiang", "Mulig", "Pampanga", "Panacan", "Poblacion District", "Sasa", "Talomo", "Tigatto", "Toril", "Tugbok"]
        }
    },
    "Region XII": {
        "South Cotabato": {
            "General Santos": ["Apopong", "Baluan", "Bula", "Buayan", "Calumpang", "City Heights", "Conel", "Dadiangas East", "Dadiangas North", "Dadiangas South", "Dadiangas West", "Fatima", "Katangawan", "Labangal", "Lagao", "Ligaya", "Mabuhay", "San Isidro", "San Jose", "Sinawal", "Tambler", "Tinagacan", "Upper Labay"]
        }
    }
};