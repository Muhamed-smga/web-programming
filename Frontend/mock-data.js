export const places = [
  {
    id: 'caffe-106',
    name: 'Caffe 106',
    category: 'Coffee Bars',
    neighborhood: 'Baščaršija',
    address: 'Sarači 16, Sarajevo',
    description: 'A relaxed stop for Bosnian coffee and a front-row seat to the old town.',
    image: 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=900&q=85',
    rating: 4.9,
    reviewCount: 128,
    hours: 'Daily, 8:00 AM – 11:00 PM',
    isNew: false,
    reviews: [
      { name: 'Lejla H.', rating: 5, comment: 'Lovely coffee and a perfect spot to watch the old town wake up.', date: 'October 12, 2026' },
      { name: 'Mark T.', rating: 5, comment: 'Warm welcome, great location, and excellent Bosnian coffee.', date: 'September 28, 2026' }
    ]
  },
  {
    id: 'dveri',
    name: 'Dveri',
    category: 'Bars',
    neighborhood: 'Baščaršija',
    address: 'Prote Bakovića 12, Sarajevo',
    description: 'A cozy, intimate dining room serving Bosnian comfort food in the heart of the bazaar.',
    image: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=900&q=85',
    rating: 4.8,
    reviewCount: 96,
    hours: 'Daily, 10:00 AM – 11:00 PM',
    isNew: false,
    reviews: [
      { name: 'Amina K.', rating: 5, comment: 'A special little place with generous portions and lovely service.', date: 'October 3, 2026' },
      { name: 'Oliver R.', rating: 4, comment: 'Great local flavors tucked away in a beautiful street.', date: 'August 19, 2026' }
    ]
  },
  {
    id: 'caffe-divan',
    name: 'Caffe Divan',
    category: 'Coffee Bars',
    neighborhood: 'Baščaršija',
    address: 'Kovači 2, Sarajevo',
    description: 'Take a slow coffee break in a quiet courtyard just above the busy bazaar.',
    image: 'https://images.unsplash.com/photo-1445116572660-236099ec97a0?auto=format&fit=crop&w=900&q=85',
    rating: 4.8,
    reviewCount: 82,
    hours: 'Daily, 9:00 AM – 10:00 PM',
    isNew: false,
    reviews: [{ name: 'Nina P.', rating: 5, comment: 'The courtyard is peaceful and the coffee is wonderful.', date: 'October 7, 2026' }]
  },
  {
    id: 'baklava-ducan',
    name: 'Baklava Dućan',
    category: 'Sweet Shops',
    neighborhood: 'Baščaršija',
    address: 'Bravadžiluk 16, Sarajevo',
    description: 'A sweet stop for flaky baklava, traditional treats, and a little Turkish coffee.',
    image: 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=900&q=85',
    rating: 4.7,
    reviewCount: 74,
    hours: 'Daily, 9:00 AM – 9:00 PM',
    isNew: false,
    reviews: [{ name: 'Sara M.', rating: 5, comment: 'Fresh, delicious baklava and friendly people behind the counter.', date: 'September 15, 2026' }]
  },
  {
    id: 'barhana',
    name: 'Barhana',
    category: 'Bars',
    neighborhood: 'Baščaršija',
    address: 'Dušanjelića čikma 1, Sarajevo',
    description: 'A lively garden courtyard for long lunches, local flavors, and easy evenings.',
    image: 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?auto=format&fit=crop&w=900&q=85',
    rating: 4.6,
    reviewCount: 63,
    hours: 'Daily, 10:00 AM – midnight',
    isNew: false,
    reviews: [{ name: 'David L.', rating: 5, comment: 'Beautiful garden and a great place to spend a warm evening.', date: 'July 20, 2026' }]
  },
  {
    id: 'silver-and-smoke',
    name: 'Silver & Smoke',
    category: 'Clubs',
    neighborhood: 'Centar',
    address: 'Skenderija 1, Sarajevo',
    description: 'An intimate late-night room with local DJs and a lively weekend crowd.',
    image: 'https://images.unsplash.com/photo-1566737236500-c8ac43014a8e?auto=format&fit=crop&w=900&q=85',
    rating: 4.5,
    reviewCount: 58,
    hours: 'Friday–Saturday, 10:00 PM – 3:00 AM',
    isNew: false,
    reviews: [{ name: 'Haris D.', rating: 5, comment: 'Great sound system and a fun, welcoming crowd.', date: 'October 1, 2026' }]
  },
  {
    id: 'zvrk',
    name: 'Zvrk',
    category: 'Sweet Shops',
    neighborhood: 'Centar',
    address: 'Ferhadija 25, Sarajevo',
    description: 'A cheerful bakery counter for a quick pastry, a cake, or a takeaway sweet.',
    image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=900&q=85',
    rating: 4.5,
    reviewCount: 42,
    hours: 'Daily, 7:00 AM – 9:00 PM',
    isNew: true,
    reviews: [{ name: 'Mia W.', rating: 5, comment: 'Stopped for one pastry and left with a box to take home.', date: 'October 15, 2026' }]
  },
  {
    id: 'kawa',
    name: 'Kawa',
    category: 'Coffee Bars',
    neighborhood: 'Marijin Dvor',
    address: 'Zmaja od Bosne 7, Sarajevo',
    description: 'A bright, modern café for carefully made coffee and a calm start to the day.',
    image: 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=85',
    rating: 4.7,
    reviewCount: 31,
    hours: 'Weekdays, 8:00 AM – 8:00 PM',
    isNew: true,
    reviews: [{ name: 'Amir S.', rating: 5, comment: 'A great flat white and a peaceful place to work for an hour.', date: 'October 9, 2026' }]
  },
  {
    id: 'mala-kuhinja',
    name: 'Mala Kuhinja',
    category: 'Bars',
    neighborhood: 'Centar',
    address: 'Branilaca Sarajeva 14, Sarajevo',
    description: 'A tiny, inventive kitchen with a warm atmosphere and a menu that changes with the day.',
    image: 'https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=900&q=85',
    rating: 4.8,
    reviewCount: 26,
    hours: 'Tuesday–Sunday, noon – 10:00 PM',
    isNew: true,
    reviews: [{ name: 'Elena B.', rating: 5, comment: 'Delightful food and genuinely kind service.', date: 'October 20, 2026' }]
  },
  {
    id: 'slatko-i-slano',
    name: 'Slatko i Slano',
    category: 'Sweet Shops',
    neighborhood: 'Grbavica',
    address: 'Grbavička 6, Sarajevo',
    description: 'Colorful cakes and pastries made for a cheerful afternoon treat.',
    image: 'https://images.unsplash.com/photo-1486427944299-d1955d23e34d?auto=format&fit=crop&w=900&q=85',
    rating: 4.6,
    reviewCount: 19,
    hours: 'Daily, 8:00 AM – 8:00 PM',
    isNew: true,
    reviews: [{ name: 'Ena J.', rating: 5, comment: 'Beautiful cakes and lots of lovely choices.', date: 'October 17, 2026' }]
  }
];

const eventDefinitions = [
  {
    id: 'jazz-under-stars',
    title: 'Jazz Under the Stars',
    category: 'Live Music',
    location: 'Skenderija Cultural Centre',
    address: 'Terezije bb, Sarajevo',
    description: 'An intimate open-air evening with Sarajevo jazz artists and guest musicians.',
    price: 'From 20 KM',
    image: 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1000&q=85',
    daysAhead: 5
  },
  {
    id: 'old-town-food-walk',
    title: 'Old Town Food Walk',
    category: 'Food & Culture',
    location: 'Baščaršija Square',
    address: 'Baščaršija, Sarajevo',
    description: 'Taste your way through the old town with a local guide and a few favorite family-run stops.',
    price: 'From 35 KM',
    image: 'https://images.unsplash.com/photo-1515003197210-e0cd7184f8b5?auto=format&fit=crop&w=1000&q=85',
    daysAhead: 8
  },
  {
    id: 'city-lights-festival',
    title: 'City Lights Festival',
    category: 'Festival',
    location: 'Vijećnica Riverside',
    address: 'Obala Kulina bana, Sarajevo',
    description: 'A weekend of live performances, local makers, and warm lights along the Miljacka.',
    price: 'Free entry',
    image: 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&w=1000&q=85',
    daysAhead: 13
  }
];

function makeEventDate(daysAhead) {
  const date = new Date();
  date.setDate(date.getDate() + daysAhead);
  date.setHours(19, 30, 0, 0);
  return date;
}

export const events = eventDefinitions.map((event) => {
  const startsAt = makeEventDate(event.daysAhead);
  return { ...event, startsAt, endsAt: new Date(startsAt.getTime() + 3 * 60 * 60 * 1000) };
});
