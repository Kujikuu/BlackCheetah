export interface LeadBasicInfo {
  firstName: string
  lastName: string
  email: string
  contactNumber: string
  country: string | null
  state: string | null
  city: string
  companyName: string
}

export interface LeadAdditionalDetails {
  leadSource: string | null
  leadStatus: string | null
  leadOwner: string | null
  lastContactedDate: string
  scheduledMeetingDate: string
  note: string
  attachments: File[]
}

export interface AddLeadData {
  basicInfo: LeadBasicInfo
  additionalDetails: LeadAdditionalDetails
}
