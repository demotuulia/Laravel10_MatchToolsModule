<?php

namespace Modules\Matches\Services;

use App\Services\UsersService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Matches\Enums\EMatchRoles;
use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Models\Matches;
use Modules\Matches\Models\MatchesForm;
use Modules\Matches\Services\Search\SearchViewService;

class DemoContentService
{

    private array $maleNames = [
        0 => 'Noah', 1 => 'Finn', 2 => 'Lucas', 3 => 'Levi', 4 => 'Milan', 5 => 'Sem', 6 => 'Noud', 7 => 'Daan',
        8 => 'Liam', 9 => 'Mees', 10 => 'Luuk', 11 => 'Luca', 12 => 'James', 13 => 'Mason', 14 => 'Bram', 15 => 'Sam',
        16 => 'Boaz', 17 => 'Guus', 18 => 'Mats', 19 => 'Lars', 20 => 'Jens', 21 => 'Zayn', 22 => 'Jake',
        23 => 'Jesse', 24 => 'Adam', 25 => 'Jurre', 26 => 'Gijs', 27 => 'Morris', 28 => 'Teun', 29 => 'Thijs',
        30 => 'Siem', 31 => 'Julian', 32 => 'Thomas', 33 => 'Stijn', 34 => 'Joep', 35 => 'Max', 36 => 'Dex',
        37 => 'Owen', 38 => 'Olivier', 39 => 'Jayden', 40 => 'Niek', 41 => 'Jack', 42 => 'Dean', 43 => 'Benjamin',
        44 => 'Jaxx', 45 => 'Floris', 46 => 'Vince', 47 => 'Ruben', 48 => 'Ryan', 49 => 'Sven', 50 => 'Mick',
        51 => 'Kai', 52 => 'Jason', 53 => 'Tijn', 54 => 'Roan', 55 => 'Ties', 56 => 'Otis', 57 => 'Joah',
        58 => 'Tobias', 59 => 'David', 60 => 'Hidde', 61 => 'Rayan', 62 => 'Xavi', 63 => 'Sepp', 64 => 'Nolan',
        65 => 'Job', 66 => 'Milo', 67 => 'Lenn', 68 => 'Fedde', 69 => 'Jace', 70 => 'Oliver', 71 => 'Nathan',
        72 => 'Ezra', 73 => 'Melle', 74 => 'Lio', 75 => 'Cas', 76 => 'Dani', 77 => 'Aiden', 78 => 'Moos',
        79 => 'Tim', 80 => 'Bodhi', 81 => 'Tygo', 82 => 'Senn', 83 => 'Hugo', 84 => 'Ted', 85 => 'Sef', 86 => 'Loek',
        87 => 'Ravi', 88 => 'Tom', 89 => 'Joshua', 90 => 'Daniël', 91 => 'Scott', 92 => 'Quinn', 93 => 'Jip',
        94 => 'Elias', 95 => 'Pepijn', 96 => 'Stef', 97 => 'Jonah', 98 => 'Mace', 99 => 'Lev',
    ];
    private array $femaleNames = [
        0 => 'Julia', 1 => 'Emma', 2 => 'Tess', 3 => 'Yara', 4 => 'Milou', 5 => 'Sophie', 6 => 'Mila', 7 => 'Evi',
        8 => 'Saar', 9 => 'Olivia', 10 => 'Liv', 11 => 'Nora', 12 => 'Zoë', 13 => 'Noor', 14 => 'Lotte', 15 => 'Lieke',
        16 => 'Maud', 17 => 'Luna', 18 => 'Nina', 19 => 'Maeve', 20 => 'Elin', 21 => 'Fleur', 22 => 'Emily',
        23 => 'Sara', 24 => 'Lynn', 25 => 'Sofie', 26 => 'Lina', 27 => 'Anna', 28 => 'Eva', 29 => 'Lauren',
        30 => 'Loïs', 31 => 'Hailey', 32 => 'Sarah', 33 => 'Fenna', 34 => 'Nova', 35 => 'Roos', 36 => 'Livia',
        37 => 'Fien', 38 => 'Isa', 39 => 'Feline', 40 => 'Romée', 41 => 'Esmee', 42 => 'Amy', 43 => 'Ivy',
        44 => 'Noé', 45 => 'Sofia', 46 => 'Lily', 47 => 'Lara', 48 => 'Elena', 49 => 'Bo', 50 => 'Floor',
        51 => 'Lizzy', 52 => 'Sophia', 53 => 'Noa', 54 => 'Jasmijn', 55 => 'Ella', 56 => 'Veerle', 57 => 'Julie',
        58 => 'Hannah', 59 => 'Cato', 60 => 'Mia', 61 => 'Liva', 62 => 'Lena', 63 => 'Chloë', 64 => 'Lisa',
        65 => 'Elise', 66 => 'Amber', 67 => 'Norah', 68 => 'Jill', 69 => 'Vera', 70 => 'Isabella', 71 => 'Novi',
        72 => 'Demi', 73 => 'Yuna', 74 => 'Naomi', 75 => 'Juul', 76 => 'Suze', 77 => 'Puck', 78 => 'Inaya',
        79 => 'Jade', 80 => 'Rosa', 81 => 'Liz', 82 => 'Juna', 83 => 'Fenne', 84 => 'Merel', 85 => 'Lize',
        86 => 'Isabel', 87 => 'Tessa', 88 => 'Jolie', 89 => 'Mae', 90 => 'Maya', 91 => 'Laura', 92 => 'Femke',
        93 => 'Charlotte', 94 => 'Mirthe', 95 => 'Benthe', 96 => 'Nola', 97 => 'Féline', 98 => 'Elisa',
        99 => 'Eline',
    ];

    private array $familyNames = [
        0 => 'de Jong', 1 => 'Jansen', 2 => 'de Vries', 3 => 'van der Berg',
        4 => 'van Dijk', 5 => 'Bakker', 6 => 'Janssen', 7 => 'Visser', 8 => 'Smit', 9 => 'Meyer',
        10 => 'de Boer', 11 => 'Mulder', 12 => 'de Groot', 13 => 'Bos', 14 => 'Vos', 15 => 'Peters',
        16 => 'Hendriks', 17 => 'van Leeuwen', 18 => 'Dekker', 19 => 'Brouwer', 20 => 'de Wit',
        21 => 'Dijkstra', 22 => 'Smits', 23 => 'de Graaf', 24 => 'van de Meer',
        25 => 'van der Linden', 26 => 'Kok', 27 => 'Jacobs', 28 => 'de Haan', 29 => 'Vermeulen',
        30 => 'van den Heuvel', 31 => 'van der Veen', 32 => 'van den Broek', 33 => 'de Bruyn',
        34 => 'de Bruin', 35 => 'van der Heijden', 36 => 'Schouten', 37 => 'van Beek',
        38 => 'Willems', 39 => 'van Vliet', 40 => 'van de Ven', 41 => 'Hoekstra',
        42 => 'Maas', 43 => 'Verhoeven', 44 => 'Koster', 45 => 'van Dam', 46 => 'van der Wal',
        47 => 'Prins', 48 => 'Blom', 49 => 'Huisman', 50 => 'Peeters', 51 => 'de Jonge', 52 => 'Kuipers',
        53 => 'van Veen', 54 => 'Post', 55 => 'Kuiper', 56 => 'Veenstra', 57 => 'Kramer', 58 => 'van den Brink',
        59 => 'Scholten', 60 => 'van Wijk', 61 => 'Postma', 62 => 'Martens', 63 => 'Vink', 64 => 'de Ruiter',
        65 => 'Timmermans', 66 => 'Groen', 67 => 'Gerritsen', 68 => 'Jonker', 69 => 'van Loon',
        70 => 'Boer', 71 => 'van der Velde', 72 => 'Willemsen',
        73 => 'Smeets', 74 => 'de Lange', 75 => 'de Vos', 76 => 'Bosch', 77 => 'van Dongen',
        78 => 'Schipper', 79 => 'de Koning', 80 => 'van der Laan', 81 => 'Koning', 82 => 'van der Velden',
        83 => 'Driessen', 84 => 'van Doorn', 85 => 'Hermans', 86 => 'Evers', 87 => 'van den Bosch',
        88 => 'van der Meulen', 89 => 'Hofman', 90 => 'Bosman', 91 => 'Wolters', 92 => 'Sanders',
        93 => 'van der Horst', 94 => 'Mol', 95 => 'Kuijpers', 96 => 'Molenaar', 97 => 'van de Pol',
        98 => 'de Leeuw', 99 => 'Verbeek',
    ];

    private array $cityNames = [
        0 => 'Amsterdam', 1 => 'Rotterdam', 2 => 'Den Haag', 3 => 'Utrecht', 4 => 'Eindhoven', 5 => 'Tilburg',
        6 => 'Groningen', 7 => 'Almere', 8 => 'Breda', 9 => 'Nijmegen', 10 => 'Enschede', 11 => 'Haarlem',
        12 => 'Arnhem', 13 => 'Zaanstad', 14 => 'Amersfoort', 15 => 'Apeldoorn', 16 => 's-Hertogenbosch',
        17 => 'Hoofddorp', 18 => 'Maastricht', 19 => 'Leiden', 20 => 'Dordrecht', 21 => 'Zoetermeer',
        22 => 'Zwolle', 23 => 'Deventer', 24 => 'Delft', 25 => 'Alkmaar', 26 => 'Heerlen', 27 => 'Venlo',
        28 => 'Leeuwarden', 29 => 'Amsterdam-Zuidoost', 30 => 'Hilversum', 31 => 'Hengelo', 32 => 'Amstelveen',
        33 => 'Roosendaal', 34 => 'Purmerend', 35 => 'Oss', 36 => 'Schiedam', 37 => 'Spijkenisse',
        38 => 'Helmond', 39 => 'Vlaardingen', 40 => 'Almelo', 41 => 'Gouda', 42 => 'Zaandam',
        43 => 'Lelystad', 44 => 'Alphen aan den Rijn', 45 => 'Hoorn', 46 => 'Velsen', 47 => 'Ede',
        48 => 'Bergen op Zoom', 49 => 'Capelle aan den IJssel', 50 => 'Assen', 51 => 'Nieuwegein',
        52 => 'Veenendaal', 53 => 'Zeist', 54 => 'Den Helder', 55 => 'Hardenberg', 56 => 'Emmen',
        57 => 'Oosterhout', 58 => 'Doetinchem', 59 => 'Kerkrade', 60 => 'Kampen', 61 => 'Weert',
        62 => 'Woerden', 63 => 'Sittard', 64 => 'Heerhugowaard', 65 => 'Rijswijk', 66 => 'Middelburg',
        67 => 'Emmeloord', 68 => 'Zwijndrecht', 69 => 'Waalwijk', 70 => 'Huizen', 71 => 'Vlissingen',
        72 => 'Ridderkerk', 73 => 'Soest', 74 => 'Roermond', 75 => 'Drachten', 76 => 'Heerenveen',
        77 => 'Medemblik', 78 => 'Tiel', 79 => 'Harderwijk', 80 => 'Maarssen', 81 => 'Venray', 82 => 'Hoogeveen',
        83 => 'Barendrecht', 84 => 'Nijkerk', 85 => 'Voorburg', 86 => 'Beverwijk', 87 => 'Goes', 88 => 'Zutphen',
        89 => 'Heemskerk', 90 => 'Wageningen', 91 => 'Castricum', 92 => 'Hoogvliet', 93 => 'Gorkum',
        94 => 'Uden', 95 => 'IJsselstein', 96 => 'Epe', 97 => 'Sneek', 98 => 'Geleen', 99 => 'Maassluis',
    ];

    private array $streetNames = [
        0 => 'Abel Tasmanstraat', 1 => 'Abraham Dolesteeg', 2 => 'Abstederdijk',
        3 => 'Acht. De Koekoek', 4 => 'Achter Clarenburg', 5 => 'Achter de Wal', 6 => 'Achter den Dom',
        7 => 'Achter St. Pieter', 8 => 'Achter Twijnstraat', 9 => 'Achterom', 10 => 'Achterstraat',
        11 => 'Achterstraat 1e', 12 => 'Achterstraat 2e', 13 => 'Achterstraat 3e', 14 => 'Adelaarstraat',
        15 => 'Admir. Van Ghentstraat', 16 => 'Adriaanstraat', 17 => 'Agnietenstraat', 18 => 'Alendorpsteeg',
        19 => 'Alphenplein (van)', 20 => 'Alphenstraat (van)', 21 => 'Amaliadwarsstraat', 22 => 'Amaliastraat',
        23 => 'Ambachtstraat', 24 => 'Amsterdamsestraatweg', 25 => 'Andreasstraat', 26 => 'Anjelierstraat',
        27 => 'Annastraat', 28 => 'Antoniedijk', 29 => 'Antoniestraat', 30 => 'Appelstraat', 31 => 'Arentzstraat',
        32 => 'Arkenspoort Zie: Bergstraat', 33 => 'Asterstraat', 34 => 'Atjehstraat 1e', 35 => 'Atjehstraat 2e',
        36 => 'Baansteeg 1e', 37 => 'Baansteeg 2e', 38 => 'Baanstraat', 39 => 'Baarssteeg', 40 => 'Badstraat',
        41 => 'Bakkerspoort Zie: Mariastraat', 42 => 'Bakkerstraat', 43 => 'Balistraat', 44 => 'Bankaplein',
        45 => 'Bankastraat', 46 => 'Bankstraat', 47 => 'Beekstraat', 48 => 'Begijnehof', 49 => 'Begijnekade',
        50 => 'Begijnesteeg 1e', 51 => 'Begijnesteeg 2e', 52 => 'Bekkerstraat', 53 => 'Bellamystraat',
        54 => 'Belthlehemscheweg', 55 => 'Bem. Weerd OZ', 56 => 'Bem. Weerd WZ', 57 => 'Bergstraat',
        58 => 'Berkstraat', 59 => 'Beukstraat', 60 => 'Beverstraat', 61 => 'Bijlhouwerstraat',
        62 => 'Billitonkade', 63 => 'Billitonstraat', 64 => 'Biltstraat', 65 => 'Biltstraat',
        66 => 'Bladstraat', 67 => 'Blauwkapelscheweg', 68 => 'Blauwsteeg', 69 => 'Bleekerskade',
        70 => 'Bleekerstraat', 71 => 'Bleekstraat', 72 => 'Bleijenburgkade', 73 => 'Bleijenburgstraat',
        74 => 'Blindesteeg Zie: Springweg', 75 => 'Bloemstraat', 76 => 'Blokstraat', 77 => 'Bokstraat',
        78 => 'Bollenhofschestraat', 79 => 'Bolstraat', 80 => 'Boogstraat', 81 => 'Boomstraat', 82 => 'Boorstraat',
        83 => 'Boothstraat', 84 => 'Borneostraat', 85 => 'Boschstraat', 86 => 'Boterstraat', 87 => 'Bouwstraat',
        88 => 'Braamstraat', 89 => 'Brakelstraat (van)', 90 => 'Brandsteeg', 91 => 'Breedstraat',
        92 => 'Bregittenstraat', 93 => 'Bremstraat', 94 => 'Brinkhof', 95 => 'Brouwerstraat', 96 => 'Brugsteeg',
        97 => 'Brugstraat', 98 => 'Burgstraat', 99 => 'Buurkerkhof', 100 => 'Buurkerksteeg 1e',
        101 => 'Buurkerksteeg 2e', 102 => 'Buurkerksteeg 3e', 103 => 'Buys Ballotstraat', 104 => 'Catharijnekade',
        105 => 'Catharijnesingel', 106 => 'Catharijnesingel', 107 => 'Catharijnesteeg', 108 => 'Cellebroederstraat',
        109 => 'Choorstraat', 110 => 'Concordiastraat', 111 => 'Corn. Evertsenstraat', 112 => 'Costa, Da kade',
        113 => 'Covelsteeg', 114 => 'Croeselaan', 115 => 'Croesestraat', 116 => 'Daalschedijk 1e',
        117 => 'Daalschedijk 2e', 118 => 'Dadelstraat', 119 => 'Daelstraat', 120 => 'Daendelsstraat',
        121 => 'Dahliastraat', 122 => 'Damstraat', 123 => 'Delistraat 1e', 124 => 'Delistraat 2e',
        125 => 'Diaconessenstraat', 126 => 'Diaconiepoort Zie: Waterstraat', 127 => 'Diemenstraat (van)',
        128 => 'Dijkstraat', 129 => 'Dillenburgstraat', 130 => 'Dirkje Mariastraat', 131 => 'Domplein',
        132 => 'Domsteeg', 133 => 'Domtoren', 134 => 'Domtrans Zie: Achter den Dom', 135 => 'Dondersstraat (F.C.)',
        136 => 'Donkere Gaard', 137 => 'Donkerstraat', 138 => 'Doornboomstraat', 139 => 'Doornstraat',
        140 => 'Dorstige-harthof Zie: Dorstige Hartsteeg', 141 => 'Dorstige-hartsteeg', 142 => 'Draaiweg',
        143 => 'Drakenburgsteeg', 144 => 'Drieharingstraat', 145 => 'Driehoek Zie: Mariahoek',
        146 => 'Driesprong', 147 => 'Drift', 148 => 'Duifstraat', 149 => 'Eikenboomstraat', 150 => 'Eikstraat',
        151 => 'Ekerenspoort Zie: Waterstraat', 152 => 'Elijnshof', 153 => 'Elimos. V. Oud Munst.',
        154 => 'Elisabethsteeg', 155 => 'Eloyensteeg', 156 => 'Emmalaan', 157 => 'Ezelsdijk', 158 => 'Ezelsdijk',
        159 => 'Fabriekstraat', 160 => 'Flieruilensteeg', 161 => 'Florastraat', 162 => 'Floris Heermalestraat',
        163 => 'Fockstraat', 164 => 'Frans Halsstraat', 165 => 'Frederik Hendrikstraat', 166 => 'Frederikastraat',
        167 => 'Frederiksoord', 168 => 'Gansstraat', 169 => 'Ganzenmarkt', 170 => 'Gasthuissteeg',
        171 => 'Geertebolwerk', 172 => 'Geertekerkhof', 173 => 'Geertestraat', 174 => 'Gerard Doustraat',
        175 => 'Gietershof', 176 => 'Gildstraat', 177 => 'Goedestraat', 178 => 'Gogschesteeg',
        179 => 'Goudsbloemstraat', 180 => 'Graanstraat', 181 => 'Grasstraat', 182 => 'Grave van Solmsstraat',
        183 => 'Grietstraat', 184 => 'Griftstraat', 185 => 'Groenesteeg', 186 => 'Groenestraat', 187 => 'Groeneweg',
        188 => 'Groote Eligensteeg', 189 => 'Gruttersdijk', 190 => 'Grutterspoort Zie: Nicolaasstraat',
        191 => 'Grutterspoort Zie: Oranjestraat', 192 => 'Grutterssteeg', 193 => 'Haagstraat', 194 => 'Hagelstraat',
        195 => 'Hamburgerstraat', 196 => 'Hamsteeg', 197 => 'Hanengeschrei Zie: Pauwstraat',
        198 => 'Hanengeschrei Zie: Vischmarkt', 199 => 'Hardebollenstraat', 200 => 'Harpstraat',
        201 => 'Hartingstraat', 202 => 'Haverland', 203 => 'Haverstraat', 204 => 'Havikstraat',
        205 => 'hazelaarstraat', 206 => 'Heemskerkstraat', 207 => 'Heerenhofje Zie: Heerenstraat',
        208 => 'Heerenstraat', 209 => 'Heerenweg', 210 => 'Heerenweg', 211 => 'Heiligeweg Zie: Steenweg',
        212 => 'Hekelsteeg', 213 => 'Helling', 214 => 'Helmstraat', 215 => 'Hemdsmouwsteeg',
        216 => 'Hendrik de Keyserstraat', 217 => 'Hennipstraat', 218 => 'Herman Saftlevenstraat',
        219 => 'Hertestraat', 220 => 'Hieronymusplein', 221 => 'Hoek v. St. Marie Zie: Mariahoek',
        222 => 'Hoenderstraat', 223 => 'Hofje van Zessen Zie: Bergstraat', 224 => 'Hofsteeg', 225 => 'Hofstraat',
        226 => 'Hoog Glagenweerd', 227 => 'Hoogeland', 228 => 'Hoogelanden OZ', 229 => 'Hoogelanden WZ',
        230 => 'Hoogenbergsteeg Zie: Vredenburg', 231 => 'Hoogenoord', 232 => 'Hoogeweidschedijk',
        233 => 'Hoogravenschedijk', 234 => 'Hoogstraat', 235 => 'Hoogt', 236 => 'Hooipoort', 237 => 'Hopakker',
        238 => 'Hopstraat', 239 => 'Horstdijk', 240 => 'Houtensche pad', 241 => 'Houtstraat', 242 => 'Hovenstraat',
        243 => 'Hugo de Grootstraat', 244 => 'Hulststraat', 245 => 'Huppeldijk', 246 => 'Iepstraat',
        247 => 'Imhoffstraat (van)', 248 => 'Inundatiekade', 249 => 'Jachtstraat', 250 => 'Jacob Ruysdaelstraat',
        251 => 'Jacobijnestraat', 252 => 'Jacobsgasthuissteeg', 253 => 'Jacobskerkhof', 254 => 'Jacobskerksteeg',
        255 => 'Jacobskerkstraat', 256 => 'Jacobsplein', 257 => 'Jacobspoort', 258 => 'Jacobssteeg',
        259 => 'Jagerskade', 260 => 'Jan Houtmanstraat', 261 => 'Jan Meyenstraat',
        262 => 'Jan Pietersz. Koenstraat', 263 => 'Jan van Scorelstraat', 264 => 'Jansdam',
    ];

    private array $companyNames = [
        0 => 'Shell', 1 => 'Coolblue', 2 => 'ASML', 3 => 'Ahold', 4 => 'Tata Steel', 5 => 'KLM', 6 => 'Bol.com',
        7 => 'BP Nederland', 8 => 'De Efteling', 9 => 'Eneco', 10 => 'De Persgroep', 11 => 'ING',
        12 => 'Royal HaskoningDHV', 13 => 'Randstad', 14 => 'Google', 15 => 'Ikea', 16 => 'Rockwool',
        17 => 'BAM', 18 => 'Achmea', 19 => 'Damen Shipyard', 20 => 'ABN Amro', 21 => 'Remeha Group', 22 => 'TenneT',
        23 => 'Coca-Cola', 24 => 'Van Leeuwen Buizen', 25 => 'Wavin', 26 => 'Rabobank', 27 => 'AkzoNobel',
        28 => 'Arcadis', 29 => 'AFAS', 30 => 'Cisco', 31 => 'DAF Trucks', 32 => 'DHL', 33 => 'Hanos',
        34 => 'Facilicom Group (o.a. Albron, Trigion en Gom)', 35 => 'Boon Edam', 36 => 'BMW Nederland',
        37 => 'The Greenery', 38 => 'Dutch Flower Group', 39 => 'Koninklijke Mosa', 40 => 'Yacht',
        41 => 'Rituals', 42 => 'Microsoft', 43 => 'Esso', 44 => '3W Vastgoed', 45 => 'Deloitte', 46 => 'Corio',
        47 => 'Voortman Steel Group', 48 => 'Agrifirm', 49 => 'Makro Nederland',
        50 => 'Nederlandse Publieke Omroep (NPO)', 51 => 'De Alliantie', 52 => 'Heijmans', 53 => 'McDonald’s',
        54 => 'ANWB', 55 => 'Van Merksteijn Steel', 56 => 'Dura Vermeer', 57 => 'Alliander', 58 => 'Unilever',
        59 => 'Enexis', 60 => 'Berenschot', 61 => 'Jumbo', 62 => 'Technische Unie', 63 => 'Havenbedrijf Rotterdam',
        64 => 'Ballast Nedam', 65 => 'RTL Nederland', 66 => 'Talpa Media', 67 => 'Blauwhoed Vastgoed', 68 => 'DSM',
        69 => 'Ymere', 70 => 'Securitas', 71 => 'Witteveen+Bos', 72 => 'NS', 73 => 'Action', 74 => 'FloraHolland',
        75 => 'Heineken', 76 => 'Nuon', 77 => 'EY', 78 => 'Dow Benelux', 79 => 'Bavaria', 80 => 'Schiphol',
        81 => 'Holland Casino', 82 => 'Binck bank', 83 => 'BDO', 84 => 'HEMA', 85 => 'Alphabet Nederland',
        86 => 'Croon Elektrotechniek', 87 => 'ASR Vastgoed ontwikkeling', 88 => 'PwC', 89 => 'Mammoet', 90 => 'KEMA',
        91 => 'IBM', 92 => 'A.S. Watson (Kruidvat)', 93 => 'KPMG', 94 => 'VodafoneZiggo', 95 => 'YoungCapital',
        96 => 'Triodos Bank', 97 => 'Aviko', 98 => 'CSU Total Care', 99 => 'G4S',
    ];

    private array $prefixes = [
        0 => '‘S', 1 => '‘s', 2 => '‘T', 3 => '’t', 4 => 'A', 5 => 'a', 6 => 'Aan', 7 => 'aan', 8 => 'Aan ’t',
        9 => 'aan ’t', 10 => 'Aan de', 11 => 'aan de', 12 => 'Aan den', 13 => 'aan den', 14 => 'Aan der',
        15 => 'aan der', 16 => 'Aan het', 17 => 'aan het', 18 => 'Aan t', 19 => 'aan t', 20 => 'Af',
        21 => 'af', 22 => 'Al', 23 => 'al', 24 => 'Am', 25 => 'am', 26 => 'Am de', 27 => 'am de',
        28 => 'Auf', 29 => 'auf', 30 => 'Auf dem', 31 => 'auf dem', 32 => 'Auf den', 33 => 'auf den',
        34 => 'Auf der', 35 => 'auf der', 36 => 'Auf ter', 37 => 'auf ter', 38 => 'Aus', 39 => 'aus',
        40 => 'Aus ‘m', 41 => 'aus ‘m', 42 => 'Aus dem', 43 => 'aus dem', 44 => 'Aus den', 45 => 'aus den',
        46 => 'Aus der', 47 => 'aus der', 48 => 'Aus m', 49 => 'aus m', 50 => 'Ben', 51 => 'ben',
        52 => 'Bij', 53 => 'bij', 54 => 'Bij ’t', 55 => 'bij ’t', 56 => 'Bij de', 57 => 'bij de',
        58 => 'Bij den', 59 => 'bij den', 60 => 'Bij het', 61 => 'bij het', 62 => 'Bij t', 63 => 'bij t',
        64 => 'Bin', 65 => 'bin', 66 => 'Boven d', 67 => 'boven d', 68 => 'Boven d’', 69 => 'boven d’', 70 => 'D',
        71 => 'd', 72 => 'D’', 73 => 'd’', 74 => 'Da', 75 => 'da', 76 => 'Dal', 77 => 'dal', 78 => 'Dal’',
        79 => 'dal’', 80 => 'Dalla', 81 => 'dalla', 82 => 'Das', 83 => 'das', 84 => 'De', 85 => 'de', 86 => 'De die',
        87 => 'de die', 88 => 'De die le', 89 => 'de die le', 90 => 'De l', 91 => 'de l', 92 => 'De l’', 93 => 'de l’',
        94 => 'De la', 95 => 'de la', 96 => 'De las', 97 => 'de las', 98 => 'De le', 99 => 'de le', 100 => 'De van der',
        101 => 'de van der', 102 => 'Deca', 103 => 'deca', 104 => 'Degli', 105 => 'degli', 106 => 'Dei', 107 => 'dei',
        108 => 'Del', 109 => 'del', 110 => 'Della', 111 => 'della', 112 => 'Den', 113 => 'den', 114 => 'Der',
        115 => 'der', 116 => 'Des', 117 => 'des', 118 => 'Di', 119 => 'di', 120 => 'Die le', 121 => 'die le',
        122 => 'Do', 123 => 'do', 124 => 'Don', 125 => 'don', 126 => 'Dos', 127 => 'dos', 128 => 'Du',
        129 => 'du', 130 => 'El', 131 => 'el', 132 => 'Het', 133 => 'het', 134 => 'I', 135 => 'i', 136 => 'Im',
        137 => 'im', 138 => 'In', 139 => 'in', 140 => 'In ’t', 141 => 'in ’t', 142 => 'In de', 143 => 'in de',
        144 => 'In den', 145 => 'in den', 146 => 'In der', 147 => 'in der', 148 => 'In het', 149 => 'in het',
        150 => 'In t', 151 => 'in t', 152 => 'L', 153 => 'l', 154 => 'L’', 155 => 'l’', 156 => 'La', 157 => 'la',
        158 => 'Las', 159 => 'las', 160 => 'Le', 161 => 'le', 162 => 'Les', 163 => 'les', 164 => 'Lo', 165 => 'lo',
        166 => 'Los', 167 => 'los', 168 => 'Of', 169 => 'of', 170 => 'Onder', 171 => 'onder', 172 => 'Onder ’t',
        173 => 'onder ’t', 174 => 'Onder de', 175 => 'onder de', 176 => 'Onder den', 177 => 'onder den',
        178 => 'Onder het', 179 => 'onder het', 180 => 'Onder t', 181 => 'onder t', 182 => 'Op', 183 => 'op',
        184 => 'Op ’t', 185 => 'op ’t', 186 => 'Op de', 187 => 'op de', 188 => 'Op den', 189 => 'op den',
        190 => 'Op der', 191 => 'op der', 192 => 'Op gen', 193 => 'op gen', 194 => 'Op het', 195 => 'op het',
        196 => 'Op t', 197 => 'op t', 198 => 'Op ten', 199 => 'op ten', 200 => 'Over', 201 => 'over',
        202 => 'Over ’t', 203 => 'over ’t', 204 => 'Over de', 205 => 'over de', 206 => 'Over den', 207 => 'over den',
        208 => 'Over het', 209 => 'over het', 210 => 'Over t', 211 => 'over t', 212 => 'S', 213 => 's',
        214 => 'S’', 215 => 's’', 216 => 'T', 217 => 't', 218 => 'Te', 219 => 'te', 220 => 'Ten', 221 => 'ten',
        222 => 'Ter', 223 => 'ter', 224 => 'Tho', 225 => 'tho', 226 => 'Thoe', 227 => 'thoe', 228 => 'Thor',
        229 => 'thor', 230 => 'To', 231 => 'to', 232 => 'Toe', 233 => 'toe', 234 => 'Tot', 235 => 'tot',
        236 => 'Uijt', 237 => 'uijt', 238 => 'Uijt ’t', 239 => 'uijt ’t', 240 => 'Uijt de', 241 => 'uijt de',
        242 => 'Uijt den', 243 => 'uijt den', 244 => 'Uijt te de', 245 => 'uijt te de', 246 => 'Uijt ten',
        247 => 'uijt ten', 248 => 'Uit', 249 => 'uit', 250 => 'Uit ’t', 251 => 'uit ’t', 252 => 'Uit de',
        253 => 'uit de', 254 => 'Uit den', 255 => 'uit den', 256 => 'Uit het', 257 => 'uit het', 258 => 'Uit t',
        259 => 'uit t', 260 => 'Uit te de', 261 => 'uit te de', 262 => 'Uit ten', 263 => 'uit ten', 264 => 'Unter',
        265 => 'unter', 266 => 'Van', 267 => 'van', 268 => 'Van ’t', 269 => 'van ’t', 270 => 'Van de',
        271 => 'van De', 272 => 'van de', 273 => 'Van de l', 274 => 'van de l', 275 => 'Van de l’',
        276 => 'van de l’', 277 => 'Van Den', 278 => 'Van den', 279 => 'van den', 280 => 'Van Der', 281 => 'Van der',
        282 => 'van der', 283 => 'Van gen', 284 => 'van gen', 285 => 'Van het', 286 => 'van het', 287 => 'Van la',
        288 => 'van la', 289 => 'Van t', 290 => 'van t', 291 => 'Van ter', 292 => 'van ter', 293 => 'Van van de',
        294 => 'van van de', 295 => 'Ver', 296 => 'ver', 297 => 'Vom', 298 => 'vom', 299 => 'Von', 300 => 'von',
        301 => 'Von ’t', 302 => 'von ’t', 303 => 'Von dem', 304 => 'von dem', 305 => 'Von den', 306 => 'von den',
        307 => 'Von der', 308 => 'von der', 309 => 'Von t', 310 => 'von t', 311 => 'Voor', 312 => 'voor',
        313 => 'Voor ’t', 314 => 'voor ’t', 315 => 'Voor de', 316 => 'voor de', 317 => 'Voor den', 318 => 'voor den',
        319 => 'Voor in ’t', 320 => 'voor in ’t', 321 => 'Voor in t', 322 => 'voor in t', 323 => 'Vor', 324 => 'vor',
        325 => 'Vor der', 326 => 'vor der', 327 => 'Zu', 328 => 'zu', 329 => 'Zum', 330 => 'zum', 331 => 'Zur'
    ];


    private array $contentWords = [
        0 => 'lorem', 1 => 'ipsum', 2 => 'dolor', 3 => 'sit', 4 => 'amet,', 5 => 'consectetur',
        6 => 'adipiscing', 7 => 'elit', 8 => 'fusce', 9 => 'nec', 10 => 'neque', 11 => 'eget',
        12 => 'dui', 13 => 'feugiat', 14 => 'ullamcorper', 15 => 'at', 16 => 'tortor', 17 => 'quis',
        18 => 'quam', 19 => 'posuere', 20 => 'cursus', 21 => 'a', 22 => 'urna', 23 => 'blandit', 24 => 'ex',
        25 => 'vehicula', 26 => 'vulputate', 27 => 'morbi', 28 => 'massa,', 29 => 'semper', 30 => 'et',
        31 => 'felis', 32 => 'semper', 33 => 'aliquam', 34 => 'sodales', 35 => 'metus', 36 => 'in', 37 => 'hac',
        38 => 'habitasse', 39 => 'platea', 40 => 'dictumst', 41 => 'nisi', 42 => 'ac', 43 => 'augue', 44 => 'orci',
        45 => 'curabitur', 46 => 'leo', 47 => 'finibus', 48 => 'ornare', 49 => 'justo', 50 => 'nec,',
        51 => 'aliquet', 52 => 'suspendisse', 53 => 'mauris', 54 => 'est', 55 => 'dapibus', 56 => 'convallis',
        57 => 'suspendisse', 58 => 'dignissim', 59 => 'velit', 60 => 'sed', 61 => 'facilisis', 62 => 'pulvinar',
        63 => 'etiam', 64 => 'eu', 65 => 'praesent', 66 => 'fringilla', 67 => 'nunc', 68 => 'velit,', 69 => 'amet',
        70 => 'porta', 71 => 'placerat', 72 => 'phasellus', 73 => 'egestas', 74 => 'nullam', 75 => 'turpis,',
        76 => 'ultrices', 77 => 'aenean', 78 => 'ligula', 79 => 'vitae', 80 => 'sem', 81 => 'iaculis', 82 => 'diam',
        83 => 'nulla', 84 => 'dictum', 85 => 'pharetra', 86 => 'vivamus', 87 => 'ut', 88 => 'sapien', 89 => 'magna',
        90 => 'faucibus', 91 => 'enim', 92 => 'lacus,', 93 => 'eget,', 94 => 'maecenas', 95 => 'non', 96 => 'massa',
        97 => 'condimentum', 98 => 'id', 99 => 'tristique', 100 => 'ante', 101 => 'imperdiet', 102 => 'potenti',
        103 => 'euismod', 104 => 'convallis,', 105 => 'lectus', 106 => 'pretium', 107 => 'donec', 108 => 'laoreet',
        109 => 'mattis', 110 => 'class', 111 => 'aptent', 112 => 'taciti', 113 => 'sociosqu', 114 => 'ad',
        115 => 'litora', 116 => 'torquent', 117 => 'per', 118 => 'conubia', 119 => 'nostra,', 120 => 'inceptos',
        121 => 'himenaeos', 122 => 'maximus', 123 => 'nunc,', 124 => 'scelerisque'
    ];

    private string $formName = 'DEMO_FORM';
    private MatchesForm $form;

    private UsersService $usersService;
    private MatchesProfileService $matchesProfileService;
    private MatchesFormService $matchesFormService;

    /**
     * @return MatchesForm
     */
    public function getForm(): MatchesForm
    {
        return $this->form;
    }


    public function __construct(
        UsersService          $usersService,
        MatchesFormService    $matchesFormService,
        MatchesProfileService $matchesProfileService,
    )
    {
        $this->usersService = $usersService;
        $this->matchesFormService = $matchesFormService;
        $this->matchesProfileService = $matchesProfileService;
    }

    public function get()
    {
        uasort($this->prefixes, function ($a, $b) {
            return strlen($b) - strlen($a);
        });
        $this->insertForm();
        $this->insertUsers(EMatchRoles::PROFESSIONAL, 50);
        $this->insertUsers(EMatchRoles::COMPANY, 5);
        $this->creteView();
    }

    private function insertForm(): void
    {//'Developer', 'Product owner', 'Manager', 'Quality engineer', 'Support  engineer',
        $form = [
            'name' => $this->formName,
            'matches' => [
                'FUNCTION' => [
                    'label' => 'Function',
                    'match_type' => EMatchType::MENU,
                    'options' => [
                        'OPTION_1' => [
                            'value' => 'Developer',
                            'order' => 0,
                        ],
                        'OPTION_2' => [
                            'value' => 'Product owner',
                            'order' => 1,
                        ],
                        'OPTION_3' => [
                            'value' => 'Manager',
                            'order' => 2,
                        ],
                        'OPTION_4' => [
                            'value' => 'Quality engineer',
                            'order' => 3,
                        ],
                        'OPTION_5' => [
                            'value' => 'Support  engineer',
                            'order' => 4,
                        ],
                    ]
                ],
                'EXPERIENCE' =>
                    [
                        'label' => 'Years of experience',
                        'match_type' => EMatchType::BIGGER_THAN,
                    ],
                'LANGUAGES' =>
                    [
                        'label' => 'Programming languages',
                        'match_type' => EMatchType::MULTIPLE_CHOOSE,
                        'options' => [
                            'OPTION_1' => [
                                'matches_name' => 'TEST_FORM_1_MULTIPLE_CHOOSE',
                                'value' => 'Php',
                                'order' => 0,
                            ],
                            'OPTION_2' => [
                                'matches_name' => 'TEST_FORM_1_MULTIPLE_CHOOSE',
                                'value' => 'Java',
                                'order' => 1,
                            ],
                            'OPTION_3' => [
                                'matches_name' => 'TEST_FORM_1_MULTIPLE_CHOOSE',
                                'value' => '.Net',
                                'order' => 2,
                            ],
                        ]
                    ],
                'DAY' => [
                    'label' => 'Favourite working day',
                    'match_type' => EMatchType::MENU,
                    'options' => [
                        ['value' => 'Monday'],
                        ['value' => 'Tuesday'],
                        ['value' => 'Wednesday'],
                    ]
                ],

                'AVAILABLE_FROM' => [
                    'label' => 'Available from',
                    'match_type' => EMatchType::DATE_FROM,
                ],
            ]
        ];
        $this->matchesFormService->store($form);

        $this->form = MatchesForm::with('matches')
            ->where('name', $this->formName)
            ->first();
    }

    private function insertUsers(EMatchRoles $role, int $amount)
    {
        for ($i = 0; $i < $amount; $i++) {
            if ($i / 10 == ceil($i / 10)) {
                echo "Inserted $role->name $i / $amount\n";
            }

            $gender = rand(0, 1);
            $name = $gender
                ? $this->femaleNames[rand(0, count($this->femaleNames) - 1)]
                : $this->maleNames[rand(0, count($this->maleNames) - 1)];
            $familyName = $this->familyNames[rand(0, count($this->familyNames) - 1)];
            $prefix = '';

            foreach ($this->prefixes as $prefixToCompare) {
                $prefixToCompare = $prefixToCompare . ' ';
                if (str_starts_with($familyName, $prefixToCompare)) {
                    $prefix = $prefixToCompare;
                    $familyName = str_replace($prefix, '', $familyName);
                }
            }
            $street = $this->streetNames[rand(0, count($this->streetNames) - 1)];
            $houseNumber = rand(1, 100);
            $postalCode = rand(1000, 9999) . chr(rand(65, 90)) . chr(rand(65, 90));
            $city = $this->cityNames[rand(0, count($this->cityNames) - 1)];
            $company = $this->companyNames[rand(0, count($this->companyNames) - 1)];
            $tel = '06' . rand(1000000, 99999999);
            $email = strtolower(
                preg_replace("/[^A-zÀ-ú0-9]+/", "", $name) .
                preg_replace("/[^A-zÀ-ú0-9]+/", "", $familyName) .
                '@' . uniqid() . '.dummy'
            );

            if ($i == 0) {
                $email = $role->value == EMatchRoles::PROFESSIONAL->value
                    ? 'testProfessional@test.nx'
                    : 'testCompany@test.nx';
            }

            $user = [
                'name' => $name,
                'familyName' => $familyName,
                'prefix' => $prefix,
                'street' => $street,
                'houseNumber' => $houseNumber,
                'postalCode' => $postalCode,
                'city' => $city,
                'company' => $company,
                'tel' => $tel,
                'email' => $email,
                'password' => '123',
                'role' => $role->value,
            ];
            $user['id'] = $this->usersService->register($user)['id'];
            $this->insertProfile($user);
        }
    }

    private function insertProfile(array $user): void
    {
        $amount = $user['role'] == 'professional' ? 1 : 10;
        for ($index = 0; $index < $amount; $index++) {
            if ($user['role'] == 'professional') {
                $name = $user['name'] . ' ';
                $name .= $user['prefix']
                    ? $user['prefix'] . ' ' . $user['familyName']
                    : $user['familyName'];
            } else {
                $name = $user['company'] . ' Vacancy ' . ($index + 1);
            }

            $params = [
                'name' => $name,
                'user_id' => $user['id'],
                'matches_form_id' => $this->form->id,
                'is_professional' => $user['role'] == EMatchRoles::PROFESSIONAL->value,
            ];

            $profile = ($user['role'] == 'professional')
                // The profile of the professional is inserted in action registerController::register
                // We know that the id is always user_id -1
                ?  $this->matchesProfileService->show($user['id'] -1 )
                : $this->matchesProfileService->store($params);

            $matches = [];

            /** @var Matches $match */
            foreach ($profile->matches() as $match) {
                $values = [];
                $value = [
                    'value_id' => $match->values->first()->id,
                ];

                $matchOptionValues = [];
                switch ($match->match_type) {
                    case EMatchType::MULTIPLE_CHOOSE->name :
                        /** @var Collection $options */
                        $options = $match->options()->get();

                        $optionIds = $options->pluck('id')->toArray();
                        $offset = rand(0, count($optionIds) - 1);
                        $length = rand(1, count($optionIds) - $offset);
                        $optionIds = array_slice($optionIds, $offset, $length);
                        sort($optionIds);
                        $value['value'] = implode(',', $optionIds);
                        $matchOptionValues = array_map(
                            function (array $option) use ($optionIds) {
                                return [
                                    'id' => $option['id'],
                                    'selected' => in_array($option['id'], $optionIds),
                                ];
                            },
                            $options->toArray()
                        );
                        $value['value'] = $value;
                        $values[] = $value;
                        break;

                    case EMatchType::MENU->name :
                        /** @var Collection $options */
                        $options = $match->options()->get();
                        $optionIds = $options->pluck('id')->toArray();
                        $optionIndex = rand(0, count($optionIds) - 1);
                        $optionId = $optionIds[$optionIndex];
                        $value['value'] = $optionId;
                        $values[] = $value;
                        break;

                    case EMatchType::DATE_FROM->name :
                        $date = Carbon::now()->addDays(rand(7, 3))->getTimestamp();
                        $value['value'] = $date;
                        $values[] = $value;
                        break;
                    default:
                        $value['value'] = rand(0, 5);
                        $values[] = $value;
                        break;
                }

                $matches[] = [
                    'id' => $match->id,
                    'match_type' => $match->match_type,
                    'values' => $values,
                    'options' => $matchOptionValues,
                ];
            }
            $params = [
                'name' => $profile->name,
                'description' => $this->getDescription(),
                'matches' => $matches
            ];
            $this->matchesProfileService->update($profile->id, $params);
        }
    }

    private function getDescription(): string
    {
        $text = '';
        for ($sentenceIndex = 0; $sentenceIndex < rand(4, 10); $sentenceIndex++) {
            $sentence = '';
            $nroOfWords = rand(10, 15);
            for ($wordIndex = 0; $wordIndex < $nroOfWords; $wordIndex++) {
                $sentence .= $this->contentWords[rand(0, count($this->contentWords) - 1)];
                if ($wordIndex < $nroOfWords - 1) {
                    $sentence .= ' ';
                }
            }
            $sentence .= '. ';
            $sentence = ucfirst($sentence);
            $text = $text . $sentence;
        }
        return $text;
    }

    private function creteView(): void
    {
        echo "\n Create View\n";
        (new SearchViewService())->createFormMatchesView($this->form->id);
    }

}
