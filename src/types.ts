export interface Booking {
  id: number;
  name: string;
  date: string;
  startTime: string;
  endTime: string;
  notes?: string;
  createdAt: string;
}

export interface NewBooking {
  name: string;
  date: string;
  startTime: string;
  endTime: string;
  notes?: string;
}
